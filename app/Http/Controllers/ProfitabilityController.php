<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Customer;
use App\Models\DealType;
use App\Models\Project;
use Carbon\Carbon;

class ProfitabilityController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function show( Request $request )
    {
        $where = '';

        if ($request->input('Customer')) {
            $where .= ' Projects.CustomerId = ' . $request->input('Customer');
        }

        if ($request->input('Project')) {
            $where .= ( $where ?  ' and ' : '' ) . ' Projects.Id = ' . $request->input('Project');
        }

        if ($request->input('DealType')) {
            $where .= ( $where ?  ' and ' : '' ) . ' Projects.DealTypeId = ' . $request->input('DealType');
        }

        if ($request->input('OnlyOpen') == 'Y') {
            $where .= ( $where ?  ' and ' : '' ) . ' Projects.ClosureDate is null ';
        }

        $month = $request->input('month');
        $year = $request->input('year');

        $dealtypes = DealType::orderBy('Description')->get();
        $customers = Customer::orderBy('Name')->get();
        $projects = Project::orderBy('Name')->get();

        if ($where && $month && $year) {

            $timesheetData = DB::select("select
    sum(iif((ActualAndForecast.TimeYear < $year) or ((ActualAndForecast.TimeYear = $year) and (ActualAndForecast.TimeMonth < $month)), ActualAndForecast.ActualDays * ActualAndForecast.ChargeRate,0.00)) as ActualChargePast,
    sum(iif(((ActualAndForecast.TimeYear = $year) and (ActualAndForecast.TimeMonth = $month)), ActualAndForecast.ActualDays * ActualAndForecast.ChargeRate,0.00)) as ActualChargeThis,
    sum(iif((ActualAndForecast.TimeYear > $year) or ((ActualAndForecast.TimeYear = $year) and (ActualAndForecast.TimeMonth > $month)), ActualAndForecast.ActualDays * ActualAndForecast.ChargeRate,0.00)) as ActualChargeFuture,
    sum(iif((ActualAndForecast.TimeYear < $year) or ((ActualAndForecast.TimeYear = $year) and (ActualAndForecast.TimeMonth < $month)), ActualAndForecast.ForecastDays * ActualAndForecast.ChargeRate,0.00)) as ForecastChargePast,
    sum(iif(((ActualAndForecast.TimeYear = $year) and (ActualAndForecast.TimeMonth = $month)), ActualAndForecast.ForecastDays * ActualAndForecast.ChargeRate,0.00)) as ForecastChargeThis,
    sum(iif((ActualAndForecast.TimeYear > $year) or ((ActualAndForecast.TimeYear = $year) and (ActualAndForecast.TimeMonth > $month)), ActualAndForecast.ForecastDays * ActualAndForecast.ChargeRate,0.00)) as ForecastChargeFuture,
    sum(iif((ActualAndForecast.TimeYear < $year) or ((ActualAndForecast.TimeYear = $year) and (ActualAndForecast.TimeMonth < $month)), ActualAndForecast.ActualDays * ActualAndForecast.CostRate,0.00)) as ActualCostPast,
    sum(iif(((ActualAndForecast.TimeYear = $year) and (ActualAndForecast.TimeMonth = $month)), ActualAndForecast.ActualDays * ActualAndForecast.CostRate,0.00)) as ActualCostThis,
    sum(iif((ActualAndForecast.TimeYear > $year) or ((ActualAndForecast.TimeYear = $year) and (ActualAndForecast.TimeMonth > $month)), ActualAndForecast.ActualDays * ActualAndForecast.CostRate,0.00)) as ActualCostFuture,
    sum(iif((ActualAndForecast.TimeYear < $year) or ((ActualAndForecast.TimeYear = $year) and (ActualAndForecast.TimeMonth < $month)), ActualAndForecast.ForecastDays * ActualAndForecast.CostRate,0.00)) as ForecastCostPast,
    sum(iif(((ActualAndForecast.TimeYear = $year) and (ActualAndForecast.TimeMonth = $month)), ActualAndForecast.ForecastDays * ActualAndForecast.CostRate,0.00)) as ForecastCostThis,
    sum(iif((ActualAndForecast.TimeYear > $year) or ((ActualAndForecast.TimeYear = $year) and (ActualAndForecast.TimeMonth > $month)), ActualAndForecast.ForecastDays * ActualAndForecast.CostRate,0.00)) as ForecastCostFuture
from
(
select Assignments.ID as AssignmentID, year(TimeEntries.TimeDate) as TimeYear, month(TimeEntries.TimeDate) as TimeMonth, sum(TimeEntries.TimeUsed) as ActualDays, 0 as ForecastDays,
    iif( ChargeCurrency.Type = 'EURO', Assignments.ChargeRate / 1.20, Assignments.ChargeRate) as ChargeRate,
    iif( CostCurrency.Type = 'EURO', Rates.DayRate / 1.20, Rates.DayRate) as CostRate
from Projects
join Assignments on Assignments.ProjectID = Projects.ID
join AspNetUsers on Assignments.UserID = AspNetUsers.Id
join TimeEntries on TimeEntries.AssignmentID = Assignments.ID and TimeEntries.TimeStatusID > 1
join CurrencyTypes ChargeCurrency on Assignments.CurrencyTypeID = ChargeCurrency.ID
left join Rates on Rates.UserId = AspNetUsers.Id and Rates.StartDate <= TimeEntries.TimeDate and Rates.EndDate >= TimeEntries.TimeDate
join CurrencyTypes CostCurrency on Rates.CurrencyTypeID = CostCurrency.ID
where $where
group by Assignments.ID, year(TimeEntries.TimeDate), month(TimeEntries.TimeDate), Assignments.ChargeRate, Rates.DayRate, ChargeCurrency.Type, CostCurrency.Type
union
select Assignments.ID as AssignmentID, year(ChargeableDayForecasts.[Month]) as TimeYear, month(ChargeableDayForecasts.[Month]) as TimeMonth, 0 as ActualDays,
        sum(ChargeableDayForecasts.ForecastDays) as ForecastDays ,
        iif( ChargeCurrency.Type = 'EURO', Assignments.ChargeRate / 1.20, Assignments.ChargeRate) as ChargeRate,
        iif( CostCurrency.Type = 'EURO', Rates.DayRate / 1.20, Rates.DayRate) as CostRate
from Projects
join Assignments on Assignments.ProjectID = Projects.ID
join AspNetUsers on Assignments.UserID = AspNetUsers.Id
join ChargeableDayForecasts on ChargeableDayForecasts.AssignmentId = Assignments.ID
join CurrencyTypes ChargeCurrency on Assignments.CurrencyTypeID = ChargeCurrency.ID
left join Rates on Rates.UserId = AspNetUsers.Id and Rates.StartDate <= ChargeableDayForecasts.[Month] and Rates.EndDate >= ChargeableDayForecasts.[Month]
join CurrencyTypes CostCurrency on Rates.CurrencyTypeID = CostCurrency.ID
where $where
group by Assignments.ID, year(ChargeableDayForecasts.[Month]), month(ChargeableDayForecasts.[Month]), Assignments.ChargeRate, Rates.DayRate, ChargeCurrency.Type, CostCurrency.Type
) ActualAndForecast
        ");

            if ($timesheetData) $timesheetData = $timesheetData[0];


            $xeroData = DB::select("select
        sum(iif((InvYear < $year or ((InvYear = $year) and (InvMonth < $month))) and (InvoiceType = 'Income'), AmountOrig,0)) as IncomeOrigPast,
        sum(iif(((InvYear = $year) and (InvMonth = $month) and (InvoiceType = 'Income')), AmountOrig,0)) as IncomeOrigThis,
        sum(iif((InvYear > $year or ((InvYear = $year) and (InvMonth > $month))) and (InvoiceType = 'Income'), AmountOrig,0)) as IncomeOrigFuture,
        sum(iif((InvYear < $year or ((InvYear = $year) and (InvMonth < $month))) and (InvoiceType = 'Income'), AmountGBP,0)) as IncomeGBPPast,
        sum(iif(((InvYear = $year) and (InvMonth = $month) and (InvoiceType = 'Income')), AmountGBP,0)) as IncomeGBPThis,
        sum(iif((InvYear > $year or ((InvYear = $year) and (InvMonth > $month))) and (InvoiceType = 'Income'), AmountGBP,0)) as IncomeGBPFuture,
        sum(iif((InvYear < $year or ((InvYear = $year) and (InvMonth < $month))) and (InvoiceType = 'Expenditure'), AmountOrig,0)) as ExpenditureOrigPast,
        sum(iif(((InvYear = $year) and (InvMonth = $month) and (InvoiceType = 'Expenditure')), AmountOrig,0)) as ExpenditureOrigThis,
        sum(iif((InvYear > $year or ((InvYear = $year) and (InvMonth > $month))) and (InvoiceType = 'Expenditure'), AmountOrig,0)) as ExpenditureOrigFuture,
        sum(iif((InvYear < $year or ((InvYear = $year) and (InvMonth < $month))) and (InvoiceType = 'Expenditure'), AmountGBP,0)) as ExpenditureGBPPast,
        sum(iif(((InvYear = $year) and (InvMonth = $month) and (InvoiceType = 'Expenditure')), AmountGBP,0)) as ExpenditureGBPThis,
        sum(iif((InvYear > $year or ((InvYear = $year) and (InvMonth > $month))) and (InvoiceType = 'Expenditure'), AmountGBP,0)) as ExpenditureGBPFuture
    from (
        select Naut_Project, Invoices.CurrencyCode,
            iif(Invoices.Type = 'ACCREC' or Invoices.Type = 'ACCRECCREDIT', 'Income', 'Expenditure') as InvoiceType, year(InvoiceDate) as InvYear, month(InvoiceDate) as InvMonth,
            iif(right(Invoices.Type,6) = 'CREDIT', -1.00, 1.00) * LineAmount as AmountOrig,
            iif(right(Invoices.Type,6) = 'CREDIT', -1.00, 1.00) * LineAmount / CurrencyRate as AmountGBP
            from Invoices
        join LineItems on Invoices.InvoiceID = LineItems.InvoiceID
        join Projects on Naut_Project = Projects.Code
        where $where
    ) InvoiceData ");

            if ($xeroData) $xeroData = $xeroData[0];

            $pAndLData = DB::select("select
        sum(iif(([Year] < $year or (([Year] = $year) and ([Month] < $month))) and (Section = 'Income'), [Value],0.00)) as PaLIncomeGBPPast,
        sum(iif((([Year] = $year) and ([Month] = $month) and (Section = 'Income')), [Value],0.00)) as PaLIncomeGBPThis,
        sum(iif(([Year] > $year or (([Year] = $year) and ([Month] > $month))) and (Section = 'Income'), [Value],0.00)) as PaLIncomeGBPFuture,

        sum(iif(([Year] < $year or (([Year] = $year) and ([Month] < $month))) and (Section = 'Less Cost of Sales'), [Value],0.00)) as PaLExpGBPPast,
        sum(iif((([Year] = $year) and ([Month] = $month) and (Section = 'Less Cost of Sales')), [Value],0.00)) as PaLExpGBPThis,
        sum(iif(([Year] > $year or (([Year] = $year) and ([Month] > $month))) and (Section = 'Less Cost of Sales'), [Value],0.00)) as PaLExpGBPFuture,

        sum(iif(([Year] < $year or (([Year] = $year) and ([Month] < $month))) and (Section = 'Less Operating Expenses'), [Value],0.00)) as PaLOverheadsGBPPast,
        sum(iif((([Year] = $year) and ([Month] = $month) and (Section = 'Less Operating Expenses')), [Value],0.00)) as PaLOverheadsGBPThis,
        sum(iif(([Year] > $year or (([Year] = $year) and ([Month] > $month))) and (Section = 'Less Operating Expenses'), [Value],0.00)) as PaLOverheadsGBPFuture
    from ProfitAndLoss
    join Projects on ProfitAndLoss.Project collate SQL_Latin1_General_CP1_CI_AS = Projects.Code
    where $where");

            if ($pAndLData) $pAndLData = $pAndLData[0];

        } else {
            $pAndLData = new \stdClass();
            $xeroData = new \stdClass();
            $timesheetData = new \stdClass();
        }

        return view ('profitability.show', [
            'dealtypes' => $dealtypes,
            'customers' => $customers,
            'projects' => $projects,
            'pAndLData' => $pAndLData,
            'xeroData' => $xeroData,
            'timesheetData' => $timesheetData,
            'dealtype' => $request->input('DealType'),
            'customer' => $request->input('Customer'),
            'project' => $request->input('Project'),
            'onlyopen' => $request->input('OnlyOpen'),
            'month' => $month,
            'year' => $year
        ]);
    }
}
