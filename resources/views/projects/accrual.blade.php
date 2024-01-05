@extends('layouts.app')

@section('style')
@endsection

@section('viewName')
    Projects
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h4>Project:&nbsp;{{ $project->Code }}:&nbsp;{{ $project->Name }}</h4>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <h5>Start Date:&nbsp;{{ $project->StartDate->format('d/m/Y') }}</h5>
                </div>
                <div class="col-md-6">
                    <h5>End Date: {{ $project->EndDate->format('d/m/Y') }}</h5>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <h5>Project Type:&nbsp;{{ $project->Type }}</h5>
                </div>
                @if ($project->Type == "Fixed Price")
                    <div class="col-md-6">
                        <h5>Project Value: £{{ number_format($project->ContractValue, 2) }}</h5>
                    </div>
                @endif
            </div>
            <div class="row">
                <div class="btn-toolbar col-md-6" role="toolbar" aria-label="Data toolbar">
                    <div class="btn-group" role="group" aria-label="Filters">
                        <button type="button" class="btn btn-secondary active" value="Revenue">Revenue</button>
                        <button type="button" class="btn btn-secondary active" value="Costs">Costs</button>
                        <button type="button" class="btn btn-secondary active" value="Profit">Profit</button>
                    </div>
                </div>
            </div>
        </div>
        <div>
            <div class="panel panel-default">
                <div class="panel-body">
                    <table id="finances" class="table table-hover table-bordered table-striped" style="width:100%">
                        <thead>
                        <tr>
                            <th>Year</th>
                            <th>Month</th>
                            <th>Invoiced (Month)</th>
                            <th>Invoiced (Cumm)</th>
                            <th>Rev Accrued (Month)</th>
                            <th>Rev Accrued (Cumm)</th>
                            <th>Total (Month)</th>
                            <th>Total (Cumm)</th>
                            <th>Time Charged (Month)</th>
                            <th>Time Charged (Cumm)</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $invoices_cumm = 0.00;
                        $purchase_invoices_cumm = 0.00;
                        $time_charge_cumm = 0.00;
                        $time_cost_cumm = 0.00;
                        $rev_accrual_cumm = 0.00;
                        $cost_accrual_cumm = 0.00;
                        ?>
                        @foreach($finances as $finance)
                            <?php
                            $invoices = is_null($finance->Invoices) ? 0.00 : $finance->Invoices;
                            $purchase_invoices = is_null($finance->PurchaseInvoices) ? 0.00 : $finance->PurchaseInvoices;
                            $time_charge = is_null($finance->PeopleCharge) ? 0.00 : $finance->PeopleCharge;
                            $time_cost = is_null($finance->PeopleCost) ? 0.00 : $finance->PeopleCost;
                            $rev_accrual = is_null($finance->RevenueAccrual) ? 0.00 : $finance->RevenueAccrual;
                            $cost_accrual = is_null($finance->CostAccrual) ? 0.00 : $finance->CostAccrual;

                            $invoices_cumm += $invoices;
                            $purchase_invoices_cumm += $purchase_invoices;
                            $time_charge_cumm += $time_charge;
                            $time_cost_cumm += $time_cost;
                            $rev_accrual_cumm += $rev_accrual;
                            $cost_accrual_cumm +=$cost_accrual;
                            ?>
                            <tr>
                                <td>
                                    {{ $finance->mYear }}
                                </td>
                                <td>
                                    {{ $finance->mMonth }}
                                </td>
                                <td>
                                    {{ number_format($invoices, 2) }}
                                </td>
                                <td>
                                    {{ number_format($invoices_cumm, 2) }}
                                </td>
                                <td>
                                    {{ number_format($rev_accrual, 2) }}
                                </td>
                                <td>
                                    {{ number_format($rev_accrual_cumm, 2) }}
                                </td>
                                <td>
                                    {{ number_format($invoices + $rev_accrual, 2) }}
                                </td>
                                <td>
                                    {{ number_format($invoices_cumm + $rev_accrual_cumm, 2) }}
                                </td>
                                <td>
                                    {{ number_format($time_charge, 2) }}
                                </td>
                                <td>
                                    {{ number_format($time_charge_cumm, 2) }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection

@section('script')
    <script type="text/javascript" defer>
        window.addEventListener('load', function () {
            oTable = $('#finances').DataTable({
                searching: false,
                ordering: false,
                scrollY:        '100%',
                scrollCollapse: true,
                paging:         false
            });
            setTableHeight();
            $(window).resize(function(){
                setTableHeight();
            });
        });

        function setTableHeight() {
            var otherHeight = $('body').height() - $('.dataTables_scrollBody').height();
            var tableHeight = $(window).height() - otherHeight - 1;
            tableHeight = Math.max(200, tableHeight);
            $('.dataTables_scrollBody').css('height', tableHeight + 'px');
        }

    </script>
@endsection
