<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Carbon\Carbon;
use DateTime;
use Validator;
use App\Models\Deal;
use App\Models\DealHistory;
use App\Models\DealNote;
use App\Models\DealStage;
use App\Models\DealSubType;
use App\Models\DealType;
use App\Models\DealSource;
use App\Models\Customer;
use App\Models\AspNetUser;
use App\Models\Contact;
use App\Models\CustomerContact;
use App\Models\Title;
use App\Lib\Convert;

class DealsController extends Controller
{
	private $yearStart;
    private $yearEnd;
	private $LYyearStart;
    private $LLYyearStart;
    private $LLYyearEnd;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
		$this->yearStart = Carbon::create(Carbon::now()->year - (Carbon::now()->month >= 11 ? 0 : 1),11,1,0,0,0);
        $this->yearEnd = Carbon::create(Carbon::now()->year + (Carbon::now()->month >= 11 ? 1 : 0) ,10,30,23,59,59);
		$this->LYyearStart = Carbon::create(Carbon::now()->year - (Carbon::now()->month >= 11 ? 0 : 1) - 1,11,1,0,0,0);
        $this->LLYyearStart = Carbon::create(Carbon::now()->year - (Carbon::now()->month >= 11 ? 0 : 1) - 2,11,1,0,0,0);
        $this->LLYyearEnd = Carbon::create(Carbon::now()->year - (Carbon::now()->month >= 11 ? 0 : 1) ,10,30,23,59,59);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index( Request $request)
    {
        // Default to what we last had if no filter specified
		$filter = $request->input('filter') ?: session('filter') ?: 'All';

		switch ($filter){

            case 'Overdue' :
                $data = $this->getDealsQuery()->where('DealStages.Description', 'NOT LIKE', '%Closed%')
                                              ->where('Deals.CloseDate', '<', Carbon::now()->startOfDay()->format('Y-m-d H:i:s'))
											  ->orderBy('Deals.CloseDate','ASC')->get();
                break;

            case 'My' :
                $data = $this->getDealsQuery()->join('AspNetUsers', 'AspNetUsers.Id', '=', 'Deals.OwnerId')
											  ->where('DealStages.Description', 'NOT LIKE', '%Closed%')
                                              ->where('AspNetUsers.Email', '=', Auth::user()->email )
											  ->orderBy('Customers.Name','ASC')->orderBy('Deals.Name','ASC')
                                              ->get();
                break;

			case 'New' :
                $data = $this->getDealsQuery()->where('DealStages.Description', 'NOT LIKE', '%Closed%')
                                              ->where('Deals.CreatedDate', '>=',
												  			Carbon::now()->subDays(14)->startOfDay()->format('Y-m-d H:i:s'))
											  ->orderBy('Deals.CreatedDate','DESC')
                                              ->get();
                break;

			case 'This' :
				$first = new Carbon('first day of this month');
				$last = new Carbon('last day of this month');

                $data = $this->getDealsQuery()->where('DealStages.Description', 'NOT LIKE', '%Closed%')
                                              ->where('Deals.CloseDate', '>=', $first->startOfDay()->format('Y-m-d H:i:s'))
                                              ->where('Deals.CloseDate', '<=', $last->endOfDay()->format('Y-m-d H:i:s'))
											  ->orderBy('Deals.CloseDate','ASC')
                                              ->get();
                break;

            case 'Next' :
				$first = new Carbon('first day of next month');
				$last = new Carbon('last day of next month');

                $data = $this->getDealsQuery()->where('DealStages.Description', 'NOT LIKE', '%Closed%')
                                              ->where('Deals.CloseDate', '>=', $first->startOfDay()->format('Y-m-d H:i:s'))
                                              ->where('Deals.CloseDate', '<=', $last->endOfDay()->format('Y-m-d H:i:s'))
											  ->orderBy('Deals.CloseDate','ASC')
                                              ->get();
                break;

            case 'Recent' :
                $data = $this->getDealsQuery()->where('DealStages.Description', 'LIKE', '%Closed%')
                                              ->where('Deals.CloseDate', '>=',
												  		Carbon::now()->subDays(14)->startOfDay()->format('Y-m-d H:i:s'))
											  ->orderBy('Deals.CloseDate','DESC')
                                              ->get();
                break;


			case 'WonYr' :
                $data = $this->getDealsQuery()->where('DealStages.Description', 'LIKE', '%Closed%')
											  ->where('DealStages.Description', 'LIKE', '%Won%')
                                              ->where('Deals.CloseDate', '>=', $this->yearStart)
											  ->orderBy('Deals.CloseDate','DESC')
                                              ->get();
                break;

			case 'LostYr' :
                $data = $this->getDealsQuery()->where('DealStages.Description', 'LIKE', '%Closed%')
											  ->where('DealStages.Description', 'NOT LIKE', '%Won%')
                                              ->where('Deals.CloseDate', '>=', $this->yearStart)
											  ->orderBy('Deals.CloseDate','DESC')
                                              ->get();
                break;

            default :
                $data = $this->getDealsQuery()->where('DealStages.Description', 'NOT LIKE', '%Closed%')
										      ->orderBy('Customers.Name','ASC')->orderBy('Deals.Name','ASC')
											  ->get();
        }

		session (['filter' => $filter]);

		return view('deals.index',
				   [ 'deals' => $data,
                     'filter' => $filter
                   ]);
    }

    public function alldata()
    {
		$deals_qry = $this->getDealsQuery();

        $data = $deals_qry->get();

        return view('deals.index',
				   [ 'deals' => $data ]);
    }

	public function edit( $id )
	{
		$deal = Deal::find($id);
		if (!$deal) {
			 return back()->with(['Fatal' => 'Invalid Deal Id' ])
						  ->withInput()
						 ;
		}

		return view('deals.edit', $this->getViewData($deal, 'EDIT'));
	}

	public function show( $id )
	{
		return redirect("/deals/$id/edit");
	}

	public function create()
	{
		$deal = new Deal;

		return view('deals.edit', $this->getViewData($deal, 'CREATE'));
	}

	public function update( Request $request , $id )
	{
		$deal = Deal::find($id);
		if (!$deal) {
			 return back()->with(['Fatal' => 'Invalid Deal Id' ])
						  ->withInput()
						 ;
		}

        $validator = $this->validateDeal($request);

		$deal = $this->saveData($deal, $request);

        if ($validator->fails()) {
            return view('deals.edit', $this->getViewData($deal, 'EDIT'))->withErrors($validator);
        }

		$deal->LastUpdatedBy = $this->getCurrentAspNetUserId();

		// Save the updates
		$deal->save();

		// Create Deal History record
		$this->createDealHistory($deal);

		// Update CustomerContact
		$this->updateCustomerContact($deal);

		// Return to summary
		return redirect('deals')
			->with('success', 'Deal updated successfully!' );

	}

	private function saveData ( $deal, $request )
	{
		$deal->CustomerId = $request->input('CustomerId');
		$deal->Name = $request->input('Name');
		$deal->Description = $request->input('Description');
		$deal->Amount = Convert::currency($request->input('Amount'));
		$deal->CloseDate = $request->input('CloseDate') ? Carbon::createFromFormat('!d/m/Y', $request->input('CloseDate')) : null;
		$deal->DealTypeId = $request->input('DealTypeId');
		$deal->DealSubTypes_id = $request->input('DealSubTypes_id');
        $deal->DealSourceId = $request->input('DealSourceId');
		$deal->DealStageId = $request->input('DealStageId');
		$deal->PipelineId = $this->getToggleValue($request->input('PipelineId'),1,2);
		$deal->MustWin = $this->getToggleValue($request->input('MustWin'),1,0);
		$deal->CurrentYearRevenueAllocation = Convert::currency($request->input('CurrentYearRevenueAllocation'));
		$deal->OwnerId = $request->input('OwnerId');
		$deal->ContactId = $request->input('ContactId');

		return ($deal);
	}

	private function getToggleValue($value, $onValue, $offValue)
	{
		return ( $value ? ($value == 'on' ? $onValue : $offValue) : $offValue );
	}

	private function getViewData( $deal, $mode )
	{
		$customers = Customer::orderby('Name','ASC')->get();

		$dealStages = DealStage::orderby('Id')->get();

		$dealTypes = DealType::orderby('Description','ASC')->get();

		$dealSubTypes = DealSubType::orderby('Description','ASC')->get();

        $dealSources = DealSource::orderby('Description','ASC')->get();

		$owners = AspNetUser::join('AspNetUserRoles','AspNetUserRoles.UserId','=','AspNetUsers.Id')
							->where('AspNetUserRoles.RoleId','=',10) // 10 = New CRM User
							->orderBy('AspNetUsers.firstName', 'ASC')
							->orderBy('AspNetUsers.lastName', 'ASC')
							->get();

		$contacts = Contact::leftjoin('CustomerContacts', 'CustomerContacts.ContactId','=','Contacts.Id')
							 ->select('Contacts.Id', 'Contacts.FirstName', 'Contacts.LastName',
									  DB::raw("STRING_AGG(CustomerContacts.CustomerId,'|') as Customers"))
							 ->groupBy('Contacts.Id', 'Contacts.FirstName', 'Contacts.LastName')
							 ->orderBy('Contacts.firstName', 'ASC')
						     ->orderBy('Contacts.lastName', 'ASC')
						     ->get();

		$titles = Title::orderBy('Titles.Value','ASC')
					   ->get();

		return   ([ 'deal' => $deal,
				    'customers' => $customers,
					'dealStages' => $dealStages,
					'dealTypes' => $dealTypes,
				    'dealSubTypes' => $dealSubTypes,
                    'dealSources' => $dealSources,
					'owners' => $owners,
					'contacts' => $contacts,
				    'titles' => $titles,
				    'mode' => $mode,
					'createdByName' => $this->getAspNetUserName($deal->CreatedBy),
					'lastUpdatedByName' => $this->getAspNetUserName($deal->LastUpdatedBy),
				   ]);
	}

	private function getAspNetUserName($userId)
	{
		$user = AspNetUser::find($userId);
		return ($user ? ($user->firstName . ' ' . $user->lastname) : null);
	}

	public function store( Request $request )
	{

		$deal = new Deal;

		$validator = $this->validateDeal($request);

		$deal = $this->saveData($deal, $request);

        if ($validator->fails()) {
        	return view('deals.edit', $this->getViewData($deal, 'CREATE'))->withErrors($validator);
        }

		$deal->CreatedBy = $this->getCurrentAspNetUserId();
		$deal->LastUpdatedBy = $this->getCurrentAspNetUserId();

		// Save the updates
		$deal->save();

		// Create Deal History record
		$this->createDealHistory($deal);

		// Update CustomerContact
		$this->updateCustomerContact($deal);

		// Return to summary
		return redirect('deals')
			->with('success', 'Deal created successfully!' );

	}

	private function updateCustomerContact( $deal )
	{
		// Check if CustomerContact record
		$cc = CustomerContact::where('CustomerId','=',$deal->CustomerId)
							 ->where('ContactId','=',$deal->ContactId)
							 ->get()->first();

		if ($cc) return (true);


		// If not, create one
		$cc = new CustomerContact;

		$cc->CustomerId = $deal->CustomerId;
		$cc->ContactId = $deal->ContactId;
		$cc->CreatedBy = $this->getCurrentAspNetUserId();
		$cc->LastUpdatedBy = $this->getCurrentAspNetUserId();

		$cc->save();

		return (true);

	}

	private function validateDeal( Request $request)
	{
		$rules = [
            'CustomerId'	=> 'required|exists:Customers,ID',
			'Name'			=> 'required|string|min:3',
			'Amount'		=> ['required',
								function ($attribute, $value, $fail) {
									$testVal = Convert::currency($value);
									if (is_numeric($testVal)) {
										if (floatval($testVal) <= 0)
											$fail('Deal Value must be greater than zero');
									} else {
										$fail('Deal Value must be a number');
									}
								}
							   ],
			'CloseDate'		=> [
								'required',
								'date_format:d/m/Y',
				function ($attribute, $value, $fail) use ($request) {
					$closeDate = DateTime::createFromFormat('d/m/Y', $value)->format('Y-m-d');
            		if (($request->DealStageId) && ($request->DealStageId >= 6)) {
						// it is Closed so ClosedDate in past
						if ( $closeDate > date('Y-m-d'))
								$fail('Close Date must be in the past for Closed Deals');
					} else {
						// it is Open so ClosedDate in future
						if ( $closeDate < date('Y-m-d'))
								$fail('Close Date must be in the future for Open Deals');
					}
            	}
			], // end of CloseDate
			'DealTypeId'	=> 'required|exists:DealTypes,Id',
			'DealSubTypes_id'	=> 'sometimes|exists:DealSubTypes,Id',
            'DealSourceId'	=> 'required|exists:DealSources,Id',
			'DealStageId'	=> 'required|exists:DealStages,Id',
			'PipelineId'	=> 'sometimes|in:on,off',
			'MustWin'		=> 'sometimes|in:on,off',
			'CurrentYearRevenueAllocation'	=> ['required',
								function ($attribute, $value, $fail) use ($request) {
									$testVal = Convert::currency($value);
									if (is_numeric($testVal)) {
										if (floatval($testVal) < 0)
											$fail('Current Year Revenue must not be negative');
										else {
											if (floatval($testVal) > floatval(Convert::currency($request->input('Amount'))))
												$fail('Current Year Revenue cannot be more than Deal Value');
										}
									} else {
										$fail('Current Year Revenue must be a number');
									}
								}
							   ],
			'OwnerId'		=> [
        						'required',
        						Rule::exists('AspNetUserRoles', 'UserId')
										->where(function ($query) {
            								return $query->where('RoleId', 10);
        								}),
								],
			'ContactId'		=> 'required|numeric|exists:Contacts,Id',
			];

        $messages = [
            'CustomerId.required'		=> 'Customer cannot be blank',
			'CustomerId.exists'			=> 'Invalid Customer name',
			'Name.required'				=> 'Deal Name cannot be blank',
			'Name.string'				=> 'Invalid Deal Name',
			'Name.min'					=> 'Deal Name must be at least 3 characters',
			'Amount.required'			=> 'Deal Value cannot be blank',
			'CloseDate.required'		=> 'Close Date cannot be blank',
			'CloseDate.date_format'		=> 'Invalid Date format for Close Date',
			'DealSubTypes_id.exists'	=> 'Invalid Deal Sub Type',
			'DealTypeId.required'		=> 'Deal Type cannot be blank',
			'DealTypeId.exists'			=> 'Invalid Deal Type',
            'DealSourceId.required'		=> 'Deal Source cannot be blank',
            'DealSourceId.exists'		=> 'Invalid Deal Source',
			'DealStageId.required'		=> 'Deal Stage cannot be blank',
			'DealStageId.exists'		=> 'Invalid Deal Stage',
			'PipelineId.in'				=> 'Invalid Competitive Indicator:' . $request->input('PipelineId'),
			'MustWin.in'				=> 'Invalid Must Win Indicator',
			'CurrentYearRevenueAllocation.required'	=> 'Current Year Revenue Allocation cannot be blank',
			'OwnerId.required'			=> 'Owner cannot be blank',
			'OwnerId.exists'			=> 'Invalid Owner',
			'ContactId.required'		=> 'Contact cannot be blank',
			'ContactId.number'			=> 'Contact Id must be a number',
			'ContactId.exists'			=> 'Invalid Contact',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

		return ($validator);
	}

	public function destroy ( $id )
	{
		$deal = Deal::find($id);
		if (!$deal) {
			 return back()->with(['Fatal' => 'Invalid Deal Id' ])
						  ->withInput()
						 ;
		}

		if ($deal->DealStageId != 8) {
			return back()->with(['Fatal' => 'Can only delete CANCELLED deals' ])
						  ->withInput()
						 ;
		}

		$dealHistories = DealHistory::where('DealId','=',$id)->delete();

		$deal->delete();

		return redirect('deals')
			->with('success', 'Deal deleted' );

	}

	public function chart ( Request $request )
	{
		// Get the year
        $year = $request->input('year');
        $year = $year ?: 'this';

        if ( $year == 'last' ) {
            $this->yearStart = $this->LYyearStart;
            $this->LYyearStart = $this->LLYyearStart;
        }

        // Validate chartfilter
		// if blank set to all
		$filter = filter_var($request->input('filter'), FILTER_VALIDATE_INT);
		$filter = $filter ?: 'All';

		$dealTypes = DealType::orderby('Description','ASC')->get();

		$firstFriday = clone $this->yearStart;
		$firstFriday->addDays(6 - $firstFriday->dayOfWeek);

		$asAtDate = Carbon::create($firstFriday->year, $firstFriday->month, $firstFriday->day, 23, 59, 59);
		$LYasAtDate = Carbon::create($firstFriday->year - 1, $firstFriday->month, $firstFriday->day, 23, 59, 59);

		$lastDate = ($year == 'this') ? Carbon::now()->addDays(6) : clone $this->LLYyearEnd ;
        $yearEnd = ($year == 'this') ? clone $this->yearEnd : clone $this->LLYyearEnd ;

		$chartData = [];

		while ($asAtDate <= $yearEnd) {
            if ($asAtDate <= $lastDate) {
                $pipeline = $this->getDashboardData($this->yearStart, $asAtDate, '<', 6, $filter);
                $closedWon = $this->getDashboardData($this->yearStart, $asAtDate, '=', 6, $filter);
                $LYclosedWon = $this->getDashboardData($this->LYyearStart, $LYasAtDate, '=', 6, $filter);
                $inYearPipeline = $this->getDashboardData($this->yearStart, $asAtDate, '<', 6, $filter, true);
                $inYearClosedWon = $this->getDashboardData($this->yearStart, $asAtDate, '=', 6, $filter, true);

                array_push($chartData, [
                    'asAtDate' => $asAtDate->format('d/m/Y'),
                    'pipeline' => $pipeline[0]->Amount,
                    'closedWon' => $closedWon[0]->Amount,
                    'LYclosedWon' => $LYclosedWon[0]->Amount,
                    'inYearPipeline' => $inYearPipeline[0]->Amount,
                    'inYearClosedWon' => $inYearClosedWon[0]->Amount,
                ]);
            } else {
                $LYclosedWon = $this->getDashboardData($this->LYyearStart, $LYasAtDate, '=', 6, $filter);
                array_push($chartData, [
                    'asAtDate' => $asAtDate->format('d/m/Y'),
                    'LYclosedWon' => $LYclosedWon[0]->Amount,
                ]);
            }

			$asAtDate->addDays(7);
			$LYasAtDate->addDays(7);
		}

		return view('deals.chart',
				   [ 'data' => $chartData,
					 'filter' => $filter,
                     'year' => $year,
					 'dealTypes' => $dealTypes,
				   ]);
	}

	private function createDealHistory($deal)
	{
		$dealHistory = new DealHistory;

		$dealHistory->Name = $deal->Name;
		$dealHistory->DealId = $deal->Id;
		$dealHistory->Description = $deal->Description;
		$dealHistory->DealStageId = $deal->DealStageId;
		$dealHistory->PipelineId = $deal->PipelineId;
		$dealHistory->DealTypeId = $deal->DealTypeId;
		$dealHistory->DealSubTypes_id = $deal->DealSubTypes_id;
        $dealHistory->DealSourceId = $deal->DealSourceId;
		$dealHistory->Amount = $deal->Amount;
		$dealHistory->CloseDate = $deal->CloseDate;
		$dealHistory->ClosedCancelledReason = $deal->ClosedCancelledReason;
		$dealHistory->CurrentYearRevenueAllocation = $deal->CurrentYearRevenueAllocation;
		$dealHistory->MustWin = $deal->MustWin;
		$dealHistory->OwnerId = $deal->OwnerId;
		$dealHistory->ContactId = $deal->ContactId;
		$dealHistory->CreatedBy = $deal->LastUpdatedBy;
		$dealHistory->LastUpdatedBy = $deal->LastUpdatedBy;

		$dealHistory->save();
	}

	private function getDealsQuery()
	{
		$histories_qry = DealHistory::select( 'DealId',
								 DB::raw("string_agg(concat(DealHistories.CreatedDate, ': ',
                                  jHistoryCreatedBy.firstName collate DATABASE_DEFAULT, ' ',
                                  jHistoryCreatedBy.lastName collate DATABASE_DEFAULT, ' - ',
                                  Pipelines.[Description] collate DATABASE_DEFAULT, ' - ',
                                  DealTypes.[Description] collate DATABASE_DEFAULT, ' - ',
                                  DealSources.[Description] collate DATABASE_DEFAULT, ' - ',
                                  DealStages.[Description] collate DATABASE_DEFAULT, ' - ',
                                  Amount), ';') as History"))
							->leftJoin('DealStages', 'DealHistories.DealStageId', '=', 'DealStages.Id')
							->leftJoin('DealTypes', 'DealHistories.DealTypeId', '=', 'DealTypes.Id')
                            ->leftJoin('DealSources', 'DealHistories.DealSourceId', '=', 'DealSources.Id')
							->leftJoin('Pipelines', 'DealHistories.PipelineId', '=', 'Pipelines.Id')
							->leftJoin('AspNetUsers AS jHistoryCreatedBy', DB::raw('DealHistories.CreatedBy collate DATABASE_DEFAULT'), '=',
                                DB::raw('jHistoryCreatedBy.Id collate DATABASE_DEFAULT'))
							->groupBy('DealId');

		$notes_qry = DealNote::select( 'DealId',
								 DB::raw("string_agg(concat(DealNotes.LastUpdatedDate, ': ',
                                 jHistoryLastUpdatedBy.firstName collate DATABASE_DEFAULT, ' ',
                                 jHistoryLastUpdatedBy.lastName collate DATABASE_DEFAULT, ' - ',
                                 Note), ';') as Note"))
							->leftJoin('AspNetUsers AS jHistoryLastUpdatedBy',
                                DB::raw('DealNotes.LastUpdatedBy collate DATABASE_DEFAULT'), '=',
                                DB::raw('jHistoryLastUpdatedBy.Id collate DATABASE_DEFAULT'))
							->groupBy('DealId');


		$deals_qry = Deal::select('Deals.Id', 'Deals.Name', 'Deals.Description',
								  'DealStages.Description as DealStage',
								  'Pipelines.Description as Pipeline',
								  'Deals.Amount', 'Deals.CloseDate',
								  'Deals.ClosedCancelledReason', 'Deals.ClosedLostReason',
								  'Deals.ClosedWonReason', 'Deals.CurrentYearRevenueAllocation',
								  'MustWin', 'Customers.Name as Customer',
								  DB::raw("concat(jOwner.firstName, ' ', jOwner.lastName) as DealOwner"),
								  DB::raw("concat(jCreatedBy.firstName, ' ', jOwner.lastName) as CreatedBy"),
								  'Deals.CreatedDate',
								  DB::raw("concat(jLastUpdatedBy.firstName, ' ', jLastUpdatedBy.lastName) as LastUpdatedBy"),
								  'Deals.LastUpdatedDate', 'DealTypes.Description as DealType',
								  DB::raw("concat(Contacts.firstName, ' ', Contacts.lastName) as Contact"),
								  'Histories.History', 'Notes.Note',
								  DB::raw("(DealStages.[Percentage] *
								  				iif(DealStages.[Percentage] = 100, 100, Pipelines.[Percentage])
											/ 10000.00) as [Percentage]")
						) // end of Select
				->leftJoin('DealStages', 'Deals.DealStageId', '=', 'DealStages.Id')
				->leftJoin('DealTypes', 'Deals.DealTypeId', '=', 'DealTypes.Id')
                ->leftJoin('DealSources', 'Deals.DealSourceId', '=', 'DealSources.Id')
				->leftJoin('Pipelines', 'Deals.PipelineId', '=', 'Pipelines.Id')
				->leftJoin('Contacts', 'Deals.ContactId', '=', 'Contacts.Id')
			    ->leftJoin('Customers', 'Deals.CustomerId', '=', 'Customers.Id')
				->leftJoin('AspNetUsers AS jOwner', 'Deals.OwnerId', '=', 'jOwner.Id')
				->leftJoin('AspNetUsers AS jCreatedBy', DB::raw('Deals.CreatedBy collate DATABASE_DEFAULT'), '=', 'jCreatedBy.Id')
				->leftJoin('AspNetUsers AS jLastUpdatedBy', DB::raw('Deals.LastUpdatedBy collate DATABASE_DEFAULT'), '=', 'jLastUpdatedBy.Id')
				->leftJoin( DB::raw('('.$histories_qry->toSql().') as Histories'),
						    function ($join) use ($histories_qry) {
								$join->on('Histories.DealId', '=', 'Deals.Id')
								     ->addBinding($histories_qry->getBindings());
							})
				->leftJoin( DB::raw('('.$notes_qry->toSql().') as Notes'),
						    function ($join) use ($notes_qry) {
								$join->on('Notes.DealId', '=', 'Deals.Id')
								     ->addBinding($notes_qry->getBindings());
							});

        return ( $deals_qry );
	}

	private function getDashboardData($startDate, $asAtDate, $dealStageComparison, $dealStageId, $filter, $inYear = null, $groupBy = null) {

		$amount = $inYear ? "CurrentYearRevenueAllocation" : "Amount";

		$records = DB::select("SELECT " . ($groupBy ? ( $groupBy . ", ") : "") . "isnull(sum(WeightedAmount),0) as Amount from (
select DealStageId, PipelineId, DealTypeId, CustomerId, OwnerId, Amount,
 iif(DealStageId < 6, DealStages.Percentage * Pipelines.Percentage * Amount /10000.00, Amount) as WeightedAmount from
(
select Deals.Id, Deals.CustomerId, Deals.Name, Deals.OwnerId,
       isnull(DealHistories.DealStageId,Deals.DealStageId) as DealStageId,
       isnull(DealHistories.PipelineId,Deals.PipelineId) as PipelineId,
       isnull(DealHistories.DealTypeId,Deals.DealTypeId) as DealTypeId,
       isnull(DealHistories.$amount,Deals.$amount) as Amount
from Deals
left join (select DealHistories.DealId, max(DealHistories.Id) as maxId from DealHistories
where CreatedDate < ?
and deleted_at is null
group by DealHistories.DealId) maxHistories
on maxHistories.DealId = Deals.Id
left join DealHistories on DealHistories.Id = maxHistories.maxId
join DealStages on Deals.DealStageId = DealStages.Id
where (Deals.CreatedDate < ? or Deals.CloseDate < ?) and

    ((DealHistories.CreatedDate is null) or (DealHistories.CreatedDate < ? ))
    and (Deals.DealStageId < 6 or Deals.CloseDate >= ? )
	and Deals.deleted_at is null
	and DealHistories.deleted_at is null
) summary
join DealStages on DealStages.Id = summary.DealStageId
join Pipelines on Pipelines.Id = summary.PipelineId
) weighted
where weighted.DealStageId $dealStageComparison ?
  and " . ($filter == 'All' ? "1 = ? " : "weighted.DealTypeId = ?") .
($groupBy ? ("group by " . $groupBy) : " ") . "
order by Amount DESC" ,[ $asAtDate->format('Y-m-d'),
						 $asAtDate->format('Y-m-d'),
						 $asAtDate->format('Y-m-d'),
						 $asAtDate->format('Y-m-d'),
						 $startDate->format('Y-m-d'),
						 $dealStageId,
						 ($filter == 'All' ? "1" : $filter),
					   ]);

		return ($records);
	}
}
