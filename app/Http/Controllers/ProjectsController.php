<?php

namespace App\Http\Controllers;

use App\Model\ProfitAndLoss;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Project;
use App\Models\ProjectType;
use Carbon\Carbon;

class ProjectsController extends Controller
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
    public function index( Request $request )
    {
        $filter = $request->input('filter') ?: 'Live';

		$data = Project::leftJoin('AspNetUsers','AspNetUsers.Id','=','Projects.UserID')
					->leftJoin('Customers','Customers.ID','=','Projects.CustomerID')
					->select('Projects.ID', 'Customers.Name as Customer','Projects.Code', 'Projects.Name',
							 'Projects.StartDate', 'Projects.EndDate', 'AspNetUsers.firstName', 'AspNetUsers.lastName')
					->where('InternalProject','=',0)
					->where(function($query) use ($filter) {
                        if ($filter == 'Live') {
                            $query->where('Projects.ClosureDate','>=',new Carbon('first day of last month'))
                                ->orWhereNull('Projects.ClosureDate');
                        } else {
                            $query->where('InternalProject', '=', 0);
                        }
					})
					->get();

        return view('projects.index',
				   [   'filter' => $filter,
                       'projects' => $data ]);
    }

	public function show( $id )
	{
		$project = Project::where('Projects.ID','=',$id)
			->join('ProjectTypes','ProjectTypes.ID','=','Projects.ProjectTypeID')
			->get()->first();

        $data = DB::select("
                select [Year], [Month], [Income] as [Revenue], [Less Cost of Sales] AS [Costs], [Less Operating Costs] AS [Overheads] from
                (
                    select [Month],[Year],[Section],[Value] from ProfitAndLoss, Projects
                    where Projects.[Code] collate SQL_Latin1_General_CP1_CI_AS  = ProfitAndLoss.Project collate SQL_Latin1_General_CP1_CI_AS
                    and Projects.ID = ?
                ) A
                PIVOT (
                    SUM([Value])
                    FOR [Section] IN ( [Income], [Less Cost of Sales], [Less Operating Costs] )
                ) PVT
                order by [Year], [Month]", [ $id ]);

		return view('projects.show',
				   [ 'project' => $project,
				     'data' => $data ]);
	}

    public function accrual( $id )
    {
        $project = Project::where('Projects.ID','=',$id)
            ->join('ProjectTypes','ProjectTypes.ID','=','Projects.ProjectTypeID')
            ->get()->first();

        $finances = DB::select("
select ID, Code, mYear, mMonth,
    sum(PurchaseInvoice) as PurchaseInvoices,
    sum(Invoice) as Invoices,
    sum(PersonCharge) as PeopleCharge,
    sum(PersonCost) as PeopleCost,
    sum(RevenueAccrual) as RevenueAccrual,
    sum(CostAccrual) as CostAccrual from
(
select 'Inv' as mType,
    Projects.ID, Projects.Code, Year(InvoiceDate) as mYear, Month(InvoiceDate) as mMonth,
    sum(iif(dbo.Invoices.Type = 'ACCREC', 1.00 , -1.00) * dbo.LineItems.LineAmount) as Invoice,
    0.00 as PurchaseInvoice,
    0.00 as PersonCharge,
    0.00 as PersonCost,
    0.00 as RevenueAccrual,
    0.00 as CostAccrual
    from LineItems
    join dbo.Invoices on dbo.LineItems.InvoiceID = dbo.Invoices.InvoiceID
    join Projects on dbo.LineItems.Naut_Project = Projects.Code
    where dbo.Invoices.Type in ('ACCREC', 'ACCRECCREDIT')
	  and dbo.Invoices.[Status] in ('PAID', 'AUTHORISED')
      and dbo.Projects.Id = ?
    group by dbo.Projects.ID, dbo.Projects.Code, year(InvoiceDate), month(InvoiceDate)
union
select 'PInv' as mType,
    Projects.ID, Projects.Code, Year(InvoiceDate) as mYear, Month(InvoiceDate) as mMonth,
    0.00 as Invoice,
    sum(iif(dbo.Invoices.Type = 'ACCPAY', 1.00 , -1.00) * dbo.LineItems.LineAmount) as PurchaseInvoice,
    0.00 as PersonCharge,
    0.00 as PersonCost,
    0.00 as RevenueAccrual,
    0.00 as CostAccrual
    from LineItems
    join dbo.Invoices on dbo.LineItems.InvoiceID = dbo.Invoices.InvoiceID
    join Projects on dbo.LineItems.Naut_Project = Projects.Code
    where dbo.Invoices.Type in ('ACCPAY', 'ACCPAYCREDIT')
	  and dbo.Invoices.[Status] in ('PAID', 'AUTHORISED')
      and dbo.Projects.Id = ?
    group by dbo.Projects.ID, dbo.Projects.Code, year(InvoiceDate), month(InvoiceDate)
UNION
select 'TimeChg' as mType,
    dbo.Projects.ID, dbo.Projects.Code, year(TimeDate) as mYear, month(TimeDate) as mMonth,
    0.00 as Invoice,
    0.00 as PurchaseInvoice,
    sum(TimeUsed * dbo.Assignments.ChargeRate) as PersonCharge,
    0.00 as PersonCost,
    0.00 as RevenueAccrual,
    0.00 as CostAccrual
from dbo.TimeEntries
join dbo.AspNetUsers on dbo.AspNetUsers.Id = dbo.TimeEntries.UserID
join dbo.Assignments on dbo.Assignments.ID = dbo.TimeEntries.AssignmentID
join dbo.Projects on dbo.Assignments.ProjectID = dbo.Projects.ID
full outer join dbo.Rates on dbo.Rates.UserId = dbo.TimeEntries.UserID
  and dbo.TimeEntries.TimeDate >= dbo.Rates.StartDate
  and dbo.TimeEntries.TimeDate <= dbo.Rates.EndDate
full outer join dbo.ProjectTypes on dbo.Projects.ProjectTypeID = dbo.ProjectTypes.ID
where dbo.Projects.ID = ?
group by dbo.Projects.ID, dbo.Projects.Code, year(TimeDate), month(TimeDate)
UNION
select 'TimeCost' as mType,
    dbo.Projects.ID, dbo.Projects.Code, year(TimeDate) as mYear, month(TimeDate) as mMonth,
    0.00 as Invoice,
    0.00 as PurchaseInvoice,
    0.00 as PersonCharge,
    sum(TimeUsed * DayRate) as PersonCost,
    0.00 as RevenueAccrual,
    0.00 as CostAccrual
from dbo.TimeEntries
join dbo.AspNetUsers on dbo.AspNetUsers.Id = dbo.TimeEntries.UserID
join dbo.Assignments on dbo.Assignments.ID = dbo.TimeEntries.AssignmentID
join dbo.Projects on dbo.Assignments.ProjectID = dbo.Projects.ID
full outer join dbo.Rates on dbo.Rates.UserId = dbo.TimeEntries.UserID
  and dbo.TimeEntries.TimeDate >= dbo.Rates.StartDate
  and dbo.TimeEntries.TimeDate <= dbo.Rates.EndDate
full outer join dbo.ProjectTypes on dbo.Projects.ProjectTypeID = dbo.ProjectTypes.ID
where dbo.Projects.ID = ?
group by dbo.Projects.ID, dbo.Projects.Code, year(TimeDate), month(TimeDate)
--) a
UNION
select 'RevAcc' as mType,
    dbo.Projects.ID, dbo.Projects.Code, [Year] as mYear, [Month] as mMonth,
    0.00 as Invoice,
    0.00 as PurchaseInvoice,
    0.00 as PersonCharge,
    0.00 as PersonCost,
    sum(RevenueAccruals.Value) as RevenueAccrual,
    0.00 as CostAccrual
from RevenueAccruals
 join dbo.Projects on dbo.RevenueAccruals.ProjectID = dbo.Projects.ID
where dbo.Projects.Id = ?
group by dbo.Projects.ID, dbo.Projects.Code, [Year], [Month]
UNION
select 'CostAcc' as mType,
    dbo.Projects.ID, dbo.Projects.Code, [Year] as mYear, [Month] as mMonth,
    0.00 as Invoice,
    0.00 as PurchaseInvoice,
    0.00 as PersonCharge,
    0.00 as PersonCost,
    0.00 as RevenueAccrual,
    sum(CostAccruals.Value) as CostAccrual
from CostAccruals
 join dbo.Assignments on dbo.CostAccruals.AssignmentID = dbo.Assignments.ID
 join dbo.Projects on dbo.Assignments.ProjectID = dbo.Projects.ID
where dbo.Projects.Id = ?
group by dbo.Projects.ID, dbo.Projects.Code, [Year], [Month]
) A
group by ID, Code, mYear, mMonth
order by ID, Code, mYear, mMonth
		", [ $id , $id, $id, $id, $id, $id ]);

        return view('projects.accrual',
            [ 'project' => $project,
                'finances' => $finances ]);

    }
}
