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
use App\Models\TimeEntry;
use App\Lib\Convert;
use App\Models\CostAccrual;
use App\Models\CostAccrualCorrection;


class CostAccrualsController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index( Request $request)
    {

		$year = intval($request->input('year'));
		$month = intval($request->input('month'));

		if ((!$year) || (!$month))
			$asAtDate = new Carbon('first day of this month');
		else {
			if ($month == 12) {
				$year++;
				$month = 1;
			} else
				$month++;
			$asAtDate = Carbon::CreateFromDate($year,$month,1);

		}
		$asAtDate->startOfDay();

		$showAccruals = (strtolower($request->input('showAccruals','false')) == 'true');
		$having = '(sum(TimeUsed * Rates.DayRate)
										- isnull(CostAccrualCorrections.Cost,0.00) '
			. ( $showAccruals ? '' : '- isnull(CostAccruals.[Value],0.00)' ) . ') <> 0';

  		$data = TimeEntry::select('Assignments.ID', 'firstName', 'lastName', 'companyName',
								  'Projects.Code', 'Projects.Name', 'CostAccruals.Status',
					DB::raw("year(TimeDate) as [Year],
							 month(TimeDate) as [Month],
							sum(TimeUsed * Rates.DayRate) as [Cost],
							isnull(CostAccrualCorrections.Cost,0.00) as [Correction],
							isnull(CostAccruals.[Value],0.00) as [Accrual],
							isnull(CostAccrualCorrections.Comment,'') as [CorrectionComment],
							isnull(CostAccruals.Comment,'') as [AccrualComment],
							isnull(CostAccruals.[Status],'') as [AccrualStatus],
							DealTypes.[Description] as DealType
							"))
					->join('AspNetUsers', 'TimeEntries.UserID', '=', 'AspNetUsers.Id')
					->join('Assignments', 'TimeEntries.AssignmentID', '=', 'Assignments.Id')
					->join('Projects', 'Assignments.ProjectID', '=', 'Projects.Id')
					->join('Rates', function ($join) {
						$join->on('Rates.UserId', '=', 'AspNetUsers.Id');
						$join->on('Rates.StartDate', '<=', 'TimeEntries.TimeDate');
						$join->on('Rates.EndDate', '>=', 'TimeEntries.TimeDate');
					})
					->leftJoin('ConsultantInvoices', function ($join) use ($asAtDate) {
						$join->on('ConsultantInvoices.UserID', '=', 'AspNetUsers.Id');
						$join->on('DateFrom', '<=', 'TimeEntries.TimeDate');
						$join->on('DateTo', '>=', 'TimeEntries.TimeDate');
						$join->whereIn('ConsultantInvoices.InvoiceStatusID', [1,2,3]);
						$join->where('ConsultantInvoices.InvoiceDate', '<', $asAtDate);
					})
        			->leftJoin('CostAccrualCorrections', function ($join) {
						$join->on('CostAccrualCorrections.AssignmentID', '=', 'Assignments.ID');
						$join->whereRaw(DB::raw('CostAccrualCorrections.[Year] = year(TimeDate)'));
						$join->whereRaw(DB::raw('CostAccrualCorrections.[Month] = month(TimeDate)'));
					})
					->leftJoin('CostAccruals', function ($join) {
						$join->on('CostAccruals.AssignmentID', '=', 'Assignments.ID');
						$join->whereRaw(DB::raw('CostAccruals.[Year] = year(TimeDate)'));
						$join->whereRaw(DB::raw('CostAccruals.[Month] = month(TimeDate)'));
					})
					->leftJoin('DealTypes', 'Projects.DealTypeId','=','DealTypes.Id')
					->where('permanent','=',0)
					->whereNull('ConsultantInvoices.ID')
					->where('TimeEntries.TimeDate', '<', $asAtDate)
					->groupBy('Assignments.ID', 'firstName', 'lastName', 'companyName',
							  'Projects.Code', 'Projects.Name', 'DealTypes.Description',
							  'CostAccrualCorrections.Cost', 'CostAccrualCorrections.Comment',
							  'CostAccruals.Value', 'CostAccruals.Comment','CostAccruals.Status',
							  DB::raw('year(TimeDate), month(TimeDate)'))
					->havingRaw($having)
					->orderBy('companyName', 'ASC')
					->orderBy('Projects.Code', 'ASC')
					->orderBy( DB::raw('year(TimeDate)'), 'ASC')
					->orderBy( DB::raw('month(TimeDate)'), 'ASC')
					->get();

		$asAtDate->subDays(1);

		return view('costaccruals.index',
				   [ 'cost_accruals' => $data,
					 'selected_year' => $asAtDate->format('Y'),
					 'selected_month' => $asAtDate->format('m'),
                   ]);
    }

	public function store( Request $request)
    {

		$rules = [
            'Month'	=> 'required|numeric|integer|between:1,12',
			'Year'	=> 'required|numeric|integer|between:2015,' . date('Y'),
			'AssignmentID' => 'required|exists:Assignments,ID',

			'Correction' => [
							'required',
							function ($attribute, $value, $fail) use ($request) {
								$testVal = Convert::currency($value);
								if (is_numeric($testVal)) {
									if (floatval($testVal) < 0)
										$fail("$attribute must not be negative");
									} else {
										$fail("$attribute must be a number");
									}
								}
							  ],
			'CorrectionComment' => [
							function ($attribute, $value, $fail) use ($request) {
								if (Convert::currency($request->input('Correction')) > 0) {
									if (strlen($value) < 10) $fail = 'Comment must be at least 10 characters';
								}
							}
								   	],
			'Accrual' => [
							'required',
							function ($attribute, $value, $fail) use ($request) {
								$testVal = Convert::currency($value);
								if (is_numeric($testVal)) {
									if (floatval($testVal) < 0)
										$fail("$attribute must not be negative");
									} else {
										$fail("$attribute must be a number");
									}
								},
							  // And not exists a posted Cost Accrual for this month
							  function ($attribute, $value, $fail) use ($request) {
								   if (CostAccrual::where('AssignmentID','=',$request->input('AssignmentID'))
								   			      ->where('Month','=',$request->input('Month'))
												  ->where('Year','=',$request->input('Year'))
									   			  ->where('Status','=','POSTED')
									   			  ->count() <> 0)
									   $fail("Existing posted Cost Accrual - contact Administrator");
							  },
							 ],
			'AccrualComment' => [
							function ($attribute, $value, $fail) use ($request) {
								if (Convert::currency($request->input('Accrual')) > 0) {
									if (strlen($value) < 10) $fail = 'Comment must be at least 10 characters';
								}
							}
								   	],

		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
        	return response()->json([
				'status' => 'failed',
				'errors' => $validator->errors(),
			]);
        }

		$warnings = [];

		$correction = Convert::currency($request->input('Correction'));
		$accrual = Convert::currency($request->input('Accrual'));

		if ($correction) {
			if (CostAccrualCorrection::where('AssignmentID','=',$request->input('AssignmentID'))
								   			      ->where('Month','=',$request->input('Month'))
												  ->where('Year','=',$request->input('Year'))
									   			  ->count() <> 0) {

				CostAccrualCorrection::where('AssignmentID','=',$request->input('AssignmentID'))
								   			      ->where('Month','=',$request->input('Month'))
												  ->where('Year','=',$request->input('Year'))
												  ->delete();
				array_push($warnings, 'Existing Cost Accrual Correction overwritten');
			}

			$costAccrualCorrection = new CostAccrualCorrection();

			$costAccrualCorrection->AssignmentID = $request->input('AssignmentID');
			$costAccrualCorrection->Year = $request->input('Year');
			$costAccrualCorrection->Month = $request->input('Month');
			$costAccrualCorrection->Cost = $correction;
			$costAccrualCorrection->Comment = $request->input('CorrectionComment');

			$costAccrualCorrection->save();
		}

		if ($accrual) {
			if (CostAccrual::where('AssignmentID','=',$request->input('AssignmentID'))
								   			      ->where('Month','=',$request->input('Month'))
												  ->where('Year','=',$request->input('Year'))
									   			  ->where('Status','<>','POSTED')
									   			  ->count() <> 0) {

				CostAccrual::where('AssignmentID','=',$request->input('AssignmentID'))
								   			      ->where('Month','=',$request->input('Month'))
												  ->where('Year','=',$request->input('Year'))
									   			  ->where('Status','<>','POSTED')
												  ->delete();
				array_push($warnings, 'Existing unposted Cost Accrual overwritten');
			}

			$costAccrual = new CostAccrual();

			$costAccrual->AssignmentID = $request->input('AssignmentID');
			$costAccrual->Year = $request->input('Year');
			$costAccrual->Month = $request->input('Month');
			$costAccrual->Value = $accrual;
			$costAccrual->Comment = $request->input('AccrualComment');

			$costAccrual->save();
		}

		return response()->json([
			'status' => 'success',
			'message' => 'Accrual updated',
			'warnings' => $warnings,
			]);
	}

}
