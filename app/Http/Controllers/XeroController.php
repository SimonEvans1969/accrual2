<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use League\OAuth2\Client\Provider\GenericProvider;
use XeroAPI\XeroPHP\JWTClaims;
use XeroAPI\XeroPHP\Configuration;
use XeroAPI\XeroPHP\Api\IdentityApi;
use XeroAPI\XeroPHP\Api\AccountingApi;
use XeroAPI\XeroPHP\Models\Accounting\ManualJournals;
use XeroAPI\XeroPHP\Models\Accounting\ManualJournal;
use XeroAPI\XeroPHP\Models\Accounting\ManualJournalLine;
use XeroAPI\XeroPHP\Models\Accounting\TrackingCategories;
use XeroAPI\XeroPHP\Models\Accounting\TrackingCategory;
use XeroAPI\XeroPHP\Models\Accounting\TrackingOptions;
use XeroAPI\XeroPHP\Models\Accounting\TrackingOption;
use GuzzleHttp\Client;
use \League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use App\Models\Invoice;
use App\Models\LineItem;
use App\Models\ProfitAndLoss;
use App\Models\Project;
use Carbon\Carbon;


class XeroController extends Controller
{
	private $accountingApi;
	private $xeroTenantId;
	private $trackingCategories;

	public function __construct()
    {
        $this->middleware('auth');
    }


    public function xero_auth()
    {
		$provider = new GenericProvider([
    		'clientId'                => config('xero.client_id'),
    		'clientSecret'            => config('xero.client_secret'),
    		'redirectUri'             => config('xero.redirectUri'),
    		'urlAuthorize'            => config('xero.urlAuthorize'),
    		'urlAccessToken'          => config('xero.urlAccessToken'),
    		'urlResourceOwnerDetails' => config('xero.urlResourceOwnerDetails')
  			]);

		$options = [
      		'scope' => [ implode(' ', config('xero.scope')) ]
					];

  		$authorizationUrl = $provider->getAuthorizationUrl($options);

		session(['oauth2_state' => $provider->getState()]);

		return redirect()->to($authorizationUrl);
	}

	public function xero_callback( Request $request )
	{
		$provider = new GenericProvider([
    		'clientId'                => config('xero.client_id'),
    		'clientSecret'            => config('xero.client_secret'),
    		'redirectUri'             => config('xero.redirectUri'),
    		'urlAuthorize'            => config('xero.urlAuthorize'),
    		'urlAccessToken'          => config('xero.urlAccessToken'),
    		'urlResourceOwnerDetails' => config('xero.urlResourceOwnerDetails')
  			]);

		if (!$request->code)
			return view('xero.index')->withError('No code in Xero Callback');

		if (!$request->state)
			return view('xero.index')->withError('No state in Xero Callback');

		if ($request->state != session('oauth2_state'))
			return view('xero.index')->withError('State mis-match in Xero Callback:' .
													$request->state . '::' . session('oauth2_state'));

		try {
		// Try to get an access token using the authorization code grant.
            $accessToken = $provider->getAccessToken('authorization_code', [
                'code' => $request->code
            ]);

            $jwt = new JWTClaims();
            $jwt->setTokenId($accessToken->getValues()["id_token"]);
            $jwt->decode();

            $config = Configuration::getDefaultConfiguration()
							->setAccessToken( (string)$accessToken->getToken() );
            $identityInstance = new IdentityApi( new Client(), $config );

            // Get Array of Tenant Ids
            $result = $identityInstance->getConnections();

            session([ 'token' 			=> $accessToken->getToken(),
					  'token_expires' 	=> $accessToken->getExpires(),
					  'tenant_id' 		=> $result[0]->getTenantId(),
					  'refresh_token' 	=> $accessToken->getRefreshToken(),
					  'id_token'		=> $accessToken->getValues()["id_token"]
					 ]);

   			return redirect('xero/get');

        } catch (IdentityProviderException $e) {
            return view('xero.callback_error')
				->withError('IdentityProviderException in Xero Callback: ' . $e->getMessage());
        }
	}

	private function setAccountingApi()
	{
		if ($this->accountingApi) return ($this->accountingApi);

		$this->xeroTenantId = session('tenant_id');

		// Check if Access Token is expired
		// if so - refresh token
		if(time() > session('token_expires')) {
			$provider = new GenericProvider([
    			'clientId'                => config('xero.client_id'),
    			'clientSecret'            => config('xero.client_secret'),
    			'redirectUri'             => config('xero.redirectUri'),
    			'urlAuthorize'            => config('xero.urlAuthorize'),
    			'urlAccessToken'          => config('xero.urlAccessToken'),
    			'urlResourceOwnerDetails' => config('xero.urlResourceOwnerDetails')
  			]);

	    	$newAccessToken = $provider->getAccessToken('refresh_token', [
	        	'refresh_token' => session('refresh_token')
	    		]);

			session([ 'token' 			=> $newAccessToken->getToken(),
					  'token_expires'	=> $newAccessToken->getExpires(),
					  'refresh_token'	=> $newAccessToken->getRefreshToken(),
					  'id_token'		=> $newAccessToken->getValues()["id_token"]
					 ]);

		}

		$config = Configuration::getDefaultConfiguration()->setAccessToken(session('token'));

		$this->accountingApi = new AccountingApi(new Client(), $config);

		return ($this->accountingApi);
	}

	// Gets all invoices and loads them into the relevant tables
	public function xero_get( Request $request )
	{
		$this->setAccountingApi();

		$modified_after = Invoice::whereIn('Type', ['ACCPAY', 'ACCREC'])
							->max('XeroUpdatedDateUTC') ?? '0000-00-00T00:00:00';

		// Added $page to make sure we get LineItems
		$invoices_in_response = 100;
		$page = 1;

		// Skip the invoices if stage not invoice
		while (($invoices_in_response == 100) && ($page <= 5))
		{
			$invoices_in_response = 0;

            $invoices = $this->accountingApi->getInvoices($this->xeroTenantId,
                                            $modified_after, null, 'UpdatedDateUTC', null, null, null, null,
                                            $page++ );

            foreach ($invoices->getInvoices() as $invoice)
            {
                $invoices_in_response++;

				$this->processInvoiceOrCreditNote($invoice, 'INV');
            }
		}

		// Reset modified date for Credit Notes
		$modified_after = Invoice::whereIn('Type', ['ACCPAYCREDIT', 'ACCRECCREDIT'])
							->max('XeroUpdatedDateUTC') ?? '0000-00-00T00:00:00';

		$invoices_in_response = 100;
		$invPage = $page;
		$page = 1;

		// Skip the invoices if stage not invoice
		while (($invoices_in_response == 100) && (($invPage + $page) <= 5))
		{
			$invoices_in_response = 0;

            $invoices = $this->accountingApi->getCreditNotes($this->xeroTenantId,
                                            $modified_after, null, 'UpdatedDateUTC',
                                            $page++ );

            foreach ($invoices->getCreditNotes() as $invoice)
            {
                $invoices_in_response++;
				$this->processInvoiceOrCreditNote($invoice, 'CRD');
            }

		}

		if (($invPage + $page) >= 5)
			return view('xero.index', [ 'connected' => true,
									    'warning' => 'Xero Load incomplete - please refresh',
									  	'message' => false]);
		else
			return view('xero.index', [ 'connected' => true,
									    'warning' => false,
									    'message' => 'Xero Load complete']);
	}

	// Gets all invoices and loads them into the relevant tables
	public function xero_get_PL( Request $request )
	{
		$month = intval($request->input('month',0));
		$year = intval($request->input('year',0));

		if (($month < 1) || ($month > 12) || ($year <2020))
					return view('xero.pandl');

		// Delete current records
		ProfitAndLoss::where('Year','=',$year)->where('Month','=',$month)->delete();

		$this->setAccountingApi();

		$startOfMonth = $year . '-' . $month . '-01';
		$endOfMonth = Carbon::parse($startOfMonth)->endOfMonth()->format('Y-m-d');

		// Times out if both tracking categories - so just project
		$profitAndLoss = $this->accountingApi->getReportProfitAndLoss(
             $this->xeroTenantId,
             $startOfMonth,
             $endOfMonth,
             null,  // $periods
             null,  // MONTH
             '8a06d1ff-43e6-4e5a-8f4f-93fa5e609e72',  // $tracking_category_id2 for Project
			 null, //'6c461eda-39eb-422e-b246-7942000d5c27', 	// tracking_category_id for Key Account
             null   // $tracking_option_id =
            );

		$projects = [];

        foreach ($profitAndLoss['reports'][0]['rows'] as $rows)
        {
            switch ($rows['row_type']) {
				case 'Header':
					foreach($rows['cells'] as $i => $cell)
					{
						$projects[$i] = $cell['value'];
					}
					break;

				case 'Section':
					$section = $rows['title'];

					foreach($rows['rows'] as $row)
					{
						$account = 'null';
						foreach($row['cells'] as $i => $cell)
						{
							if ($i == 0) {
								if (!$cell['attributes']) break;
								$account = $cell['value'];
							} else {
								if ($projects[$i] != 'Total')
                                    $PAndL = ProfitAndLoss::create([
                                        'Year' => $year,
                                        'Month' => $month,
                                        'Section' => $section,
                                        'Account' => $account,
                                        'Project' => $projects[$i],
                                        'Value' => ((($section == 'Income') ? 1.0 : -1.0 ) * $cell['value'])
                                    ]);
							}
						}
					}

			}
        }

		return view('xero.pandl', [
			"message" => "Profit and Loss processed $month / $year"
			]);
	}

	private function processInvoiceOrCreditNote($invoice, $type)
	{
		switch ($type)
		{
			case 'INV' :
				$invoice_id = $invoice['invoice_id'];
				$invoice_number = $invoice['invoice_number'];
				break;

			case 'CRD' :
				$invoice_id = $invoice['credit_note_id'];
				$invoice_number = $invoice['credit_note_number'];
				break;
		}

		// Insert / update the record
		Invoice::updateOrCreate(
            [ 	'InvoiceID' => $invoice_id ],
            [
                'Type' 				=> $invoice['type'],
                'ContactName' 		=> $invoice['contact']['name'],
                'InvoiceDate' 		=> $invoice['date'],
                'Status' 			=> $invoice['status'],
                'LineAmountTypes'	=> $invoice['line_amount_types'],
                'SubTotal'			=> ($invoice['sub_total'] ?? 0.00),
                'TotalTax'			=> ($invoice['total_tax'] ?? 0.00),
                'Total'				=> ($invoice['total'] ?? 0.00),
                'TotalDiscount'		=> ($invoice['total_discount'] ?? 0.00),
                'CurrencyCode'		=> $invoice['currency_code'],
                'CurrencyRate'		=> $invoice['currency_rate'],
                'InvoiceNumber'		=> $invoice_number,
                'Reference'			=> $invoice['reference'],
                'AmountDue'			=> ($invoice['amount_due'] ?? 0.00),
                'AmountPaid'		=> ($invoice['amount_paid'] ?? 0.00),
                'AmountCredited'	=> ($invoice['amount_credited'] ?? 0.00),
				'RemainingCredit'	=> ($invoice['remaining_credit'] ?? 0.00),
				'XeroUpdatedDateUTC'=> $invoice['updated_date_utc'],

            ]);

        // Delete any existing Line Items and replace them
        LineItem::where('InvoiceId','=', $invoice_id )->delete();

		$line_item_no = 1;

        foreach ($invoice['line_items'] as $lineItem)
        {
            // Get the tracking codes
            $trackingAccount = null;
            $trackingProject = null;

            foreach ($lineItem['tracking'] as $tracking)
            {
                switch ($tracking['name'])
                {
                    case 'Key Account' :
                        $trackingAccount = $tracking['option'];
                        break;
                    case 'Project' :
                        $trackingProject = $tracking['option'];
                        break;
                    default :
                        break;
                }
            }

			if (($type == 'CRD') && ($lineItem['line_item_id'] == null))
				$line_item_id = $invoice['credit_note_id'] . str_pad($line_item_no++, 4, '0', STR_PAD_LEFT);
			else
				$line_item_id = $lineItem['line_item_id'];

            LineItem::create(
                [
                    'InvoiceID'		=> $invoice_id,
                    'Description'	=> $lineItem['description'],
                    'Quantity'		=> $lineItem['quantity'],
                    'UnitAmount'	=> $lineItem['unit_amount'],
                    'ItemCode'		=> $lineItem['item_code'],
                    'AccountCode'	=> $lineItem['account_code'],
                    'LineItemID'	=> $line_item_id,
                    'TaxType'		=> $lineItem['tax_type'],
                    'TaxAmount'		=> ($lineItem['tax_amount'] ?? 0.00),
                    'LineAmount'	=> ($lineItem['line_amount'] ?? 0.00),
                    'DiscountRate'	=> ($lineItem['discount_rate'] ?? 0.00),
                    'DiscountAmount'=> ($lineItem['discount_amount'] ?? 0.00),
                    'Naut_Account'	=> $trackingAccount,
                    'Naut_Project'	=> $trackingProject
                ]);
        }
	}

	private function postJournal( $manual_journal )
	{
		// Convert XeroAPI\XeroPHP\Models\Accounting\ManualJournal to ManualJournals


		// Make the call
		$this->setAccountingApi();

		$response = $this->accountingApi->createManualJournals($this->xeroTenantId, $manual_journals);

		return($response);

	}

	private function createJournal ( $narration, $date ) {

		$date_utc = new \DateTime("now", new \DateTimeZone("UTC"));

		$manual_journal = new ManualJournal([
			'line_amount_types' => 'NoTax',
			'status' => 'POSTED',
			'url' => 'accruals.nautilus-consulting.co.uk',
			'show_on_cash_basis_reports' => 'true',
			'has_attachments' => 'false',
			'journal_lines' => null,
			'updated_date_utc' => $date_utc->format(\DateTime::RFC850),
			'narration' => $narration,
			'date' => $date,
			'narration' => $narration,
		]);

		return ($manual_journal);
	}

	private function addTrackingCategory ( $categoryName, $optionName )
	{
		$trackingCategory = $this->getTrackingCategory($categoryName);

		if ($trackingCategory)
		{
			$trackingOption = $this->getTrackingOption($trackingCategory, $optionName);

			if ($trackingOption)
			{
				return (new TrackingCategory([
					'tracking_category_id' => $trackingOption->getTrackingCategoryId(),
					'tracking_option_id' => $trackingOption->getTrackingOptionId()
				]));
			}
		}

		return(null);
	}

	private function getTrackingCategories()
	{
		$this->setAccountingApi();

		$this->trackingCategories = $this->accountingApi->getTrackingCategories($this->xeroTenantId);
	}

	private function getTrackingCategory($name)
	{
		foreach ($this->trackingCategories as $trackingCateory)
		{
			if ($trackingCategory->getName() == $name)
				return($trackingCategory);
		}
		return(null);
	}

	private function getTrackingOption($trackingCategory, $name)
	{
		// Check if this Option exists
		foreach ($trackingCategory->Options as $trackingOption)
		{
			if ($trackingOption->getName() == $name)
				return($trackingOption);
		}

		// If not create a tracking option
		$this->setAccountingApi();
		$trackingOptions = $this->accountingApi->createTrackingOptions(
			$this->xeroTenantId,
			$trackingCategory->getTrackingCategoryId(),
			new TrackingOption ([
				'name' => $name,
				'tracking_category_id' => $trackingCategory->getTrackingCategoryId()
			])
		);

		// Refresh the tracking categories
		$this->trackingCategories = $this->accountingApi->getTrackingCategories($this->xeroTenantId);

		// Then return the trackingOption (assuming it was set)
		foreach ($trackingOptions as $trackingOption)
		{
			if ($trackingOption->getName() == $name) return($trackingOption);
		}

		return(null);
	}

	public function test()
	{
		$this->setAccountingApi();

		$trackingOption = $this->accountingApi->createTrackingOptions(
			$this->xeroTenantId,
			'6c461eda-39eb-422e-b246-7942000d5c27',
			new TrackingOption ([
				'name' => 'TEST3',
				'tracking_category_id' => '6c461eda-39eb-422e-b246-7942000d5c27'
			])
		);


		print_r($trackingOption);
		exit(2);
	}

}
