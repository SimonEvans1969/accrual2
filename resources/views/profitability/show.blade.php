@extends('layouts.app')

@section('style')
@endsection

@section('viewName')
    Projects Profitability
@endsection

@section('content')
    <div class="container">
        <form id="filters" action="projectsprofit" method="get" class="row">
            <div class="row my-3">
                <select class="browser-default custom-select reload col-2 mx-3 filter" name="DealType" >
                    <option {{ $dealtype ? '' : ' selected ' }} value="">All service lines</option>
                    @foreach($dealtypes as $opt)
                        <option class="DealTypeOpt" value="{{ $opt->Id }}" {{ $opt->Id == $dealtype ? ' selected ' : '' }} >{{ $opt->Description }}</option>
                    @endforeach
                </select>
                <select class="browser-default custom-select reload col-3 mx-3 filter" name="Customer" >
                    <option {{ $customer ? '' : ' selected ' }} value="">All customers</option>
                    @foreach($customers as $opt)
                        <option class="CustomerOpt" value="{{ $opt->ID }}" {{ $opt->ID == $customer ? ' selected ' : '' }} >{{ $opt->Name }}</option>
                    @endforeach
                </select>
                <select class="browser-default custom-select reload col-3 mx-3 ProjectSelect" name="Project" >
                    <option customer="*" dealtype="*" {{ $project ? '' : ' selected ' }} value="">All projects</option>
                    @foreach($projects as $opt)
                        <option value="{{ $opt->ID }}" {{ $opt->ID == $project ? ' selected ' : '' }}
                                customer="{{ $opt->CustomerID }}" dealtype="{{ $opt->DealTypeId }}" live="{{ $opt->ClosureDate ? 'N' : 'Y' }}">
                                {{ $opt->Name }}</option>
                    @endforeach
                </select>
                <div class="btn-toolbar col-2 mx-3" role="toolbar" aria-label="Filter toolbar">
                    <div class="btn-group" role="group" aria-label="Filters">
                        <button type="button" name="onlyopen" class="btn btn-secondary filter {{ $onlyopen == 'Y' ? 'active' : '' }}" value="Y">Live</button>
                        <button type="button" name="onlyopen" class="btn btn-secondary filter {{ $onlyopen == 'N' ? 'active' : '' }}" value="N">All</button>
                    </div>
                </div>
            </div>
            <div class="row my-3">
                <span class="col-2 mx-1">As at:</span>
                <select class="browser-default custom-select reload col-2 ms-3" name="year">
                    <option disabled >Pick a year...</option>

                    <?php
                    $yr = 2022;
                    $curr_year = intval(date('Y'));
                    while ($yr <= $curr_year) {
                        echo '<option' . (($year == $yr) ? ' selected ' : '' ) . ">$yr</option>";
                        $yr++;
                    }
                    ?>
                </select>
                <span class="mx-3">/</span>
                <select class="browser-default custom-select reload col-2 me-3" name="month">
                    <option disabled >Pick a month...</option>
                    <option value="01" {{ $month == 1 ? ' selected ' : '' }}>Jan</option>
                    <option value="02" {{ $month == 2 ? ' selected ' : '' }}>Feb</option>
                    <option value="03" {{ $month == 3 ? ' selected ' : '' }}>Mar</option>
                    <option value="04" {{ $month == 4 ? ' selected ' : '' }}>Apr</option>
                    <option value="05" {{ $month == 5 ? ' selected ' : '' }}>May</option>
                    <option value="06" {{ $month == 6 ? ' selected ' : '' }}>Jun</option>
                    <option value="07" {{ $month == 7 ? ' selected ' : '' }}>Jul</option>
                    <option value="08" {{ $month == 8 ? ' selected ' : '' }}>Aug</option>
                    <option value="09" {{ $month == 9 ? ' selected ' : '' }}>Sep</option>
                    <option value="10" {{ $month == 10 ? ' selected ' : '' }}>Oct</option>
                    <option value="11" {{ $month == 11 ? ' selected ' : '' }}>Nov</option>
                    <option value="12" {{ $month == 12 ? ' selected ' : '' }}>Dec</option>
                </select>
                <span id="button_span" class="col-2 mx-3 hidden">
                    <button type="submit" class="btn btn-success btn-save">Submit</button>
                </span>
            </div>
        </form>
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <table id="project_history" class="table table-hover table-bordered table-striped" style="width:100%">
                            <thead>
                            <tr>
                                <th class="col-3"></th>
                                <th class="col-2">Previous</th>
                                <th class="col-2">This Month Forecast</th>
                                <th class="col-2">This Month Actual</th>
                                <th class="col-2">Future</th>
                            </tr>
                            </thead>
                            <tbody>
                                @if(isset($timesheetData->ActualChargePast))
                                <tr>
                                    <td>Timetracker Revenue GBP</td>
                                    <td class="text-right">£{{ number_format($timesheetData->ActualChargePast,2,'.',',') }}</td>
                                    <td class="text-right">£{{ number_format($timesheetData->ForecastChargeThis,2,'.',',') }}</td>
                                    <td class="text-right">£{{ number_format($timesheetData->ActualChargeThis,2,'.',',') }}</td>
                                    <td class="text-right">£{{ number_format($timesheetData->ForecastChargeFuture,2,'.',',') }}</td>
                                </tr>
                                <tr>
                                    <td>Timetracker Direct Cost GBP</td>
                                    <td class="text-right">£{{ number_format($timesheetData->ActualCostPast,2,'.',',') }}</td>
                                    <td class="text-right">£{{ number_format($timesheetData->ForecastCostThis,2,'.',',') }}</td>
                                    <td class="text-right">£{{ number_format($timesheetData->ActualCostThis,2,'.',',') }}</td>
                                    <td class="text-right">£{{ number_format($timesheetData->ForecastCostFuture,2,'.',',') }}</td>
                                </tr>
                                @endif
                                @if(isset($xeroData->IncomeGBPPast))
                                <tr>
                                    <td>Xero Revenue Invoiced</td>
                                    <td class="text-right">£{{ number_format($xeroData->IncomeGBPPast,2,'.',',') }}</td>
                                    <td></td>
                                    <td class="text-right">£{{ number_format($xeroData->IncomeGBPThis,2,'.',',') }}</td>
                                    <td class="text-right">£{{ number_format($xeroData->IncomeGBPFuture,2,'.',',') }}</td>
                                </tr>
                                @endif
                                @if(isset($pAndLData->PaLIncomeGBPPast))
                                <tr>
                                    <td>Xero Revenue Accrued</td>
                                    <td class="text-right">£{{ number_format($pAndLData->PaLIncomeGBPPast - $xeroData->IncomeGBPPast,2,'.',',') }}</td>
                                    <td></td>
                                    <td class="text-right">£{{ number_format($pAndLData->PaLIncomeGBPThis - $xeroData->IncomeGBPThis,2,'.',',') }}</td>
                                    <td class="text-right">£{{ number_format($pAndLData->PaLIncomeGBPFuture - $xeroData->IncomeGBPFuture,2,'.',',') }}</td>
                                </tr>
                                @endif
                                @if(isset($xeroData->IncomeGBPPast))
                                <tr>
                                    <td>Xero Costs Incurred</td>
                                    <td class="text-right">£{{ number_format($xeroData->ExpenditureGBPPast,2,'.',',') }}</td>
                                    <td></td>
                                    <td class="text-right">£{{ number_format($xeroData->ExpenditureGBPThis,2,'.',',') }}</td>
                                    <td class="text-right">£{{ number_format($xeroData->ExpenditureGBPFuture,2,'.',',') }}</td>
                                </tr>
                                @endif
                                @if(isset($pAndLData->PaLIncomeGBPPast) && isset($xeroData->IncomeGBPPast))
                                <tr>
                                    <td>Xero Costs Accrued</td>
                                    <td class="text-right">£{{ number_format($pAndLData->PaLExpGBPPast - $xeroData->ExpenditureGBPPast,2,'.',',') }}</td>
                                    <td></td>
                                    <td class="text-right">£{{ number_format($pAndLData->PaLExpGBPThis - $xeroData->ExpenditureGBPThis,2,'.',',') }}</td>
                                    <td class="text-right">£{{ number_format($pAndLData->PaLExpGBPFuture - $xeroData->ExpenditureGBPFuture,2,'.',',') }}</td>
                                </tr>
                                @endif
                                @if(isset($pAndLData->PaLIncomeGBPPast))
                                <tr>
                                    <td>Xero Overheads</td>
                                    <td class="text-right">£{{ number_format($pAndLData->PaLOverheadsGBPPast,2,'.',',') }}</td>
                                    <td></td>
                                    <td class="text-right">£{{ number_format($pAndLData->PaLOverheadsGBPThis,2,'.',',') }}</td>
                                    <td class="text-right">£{{ number_format($pAndLData->PaLOverheadsGBPFuture,2,'.',',') }}</td>
                                </tr>
                                @endif
                                @if(isset($timesheetData->ActualChargePast))
                                <tr>
                                    <td>Timetracker Margin</td>
                                    <td class="text-center">{{ $timesheetData->ActualChargePast != 0.00 ? number_format(($timesheetData->ActualChargePast - $timesheetData->ActualCostPast) * 100.00 / $timesheetData->ActualChargePast,2,'.',',') : '-' }}%</td>
                                    <td class="text-center">{{ $timesheetData->ForecastChargeThis != 0.00 ? number_format(($timesheetData->ForecastChargeThis - $timesheetData->ForecastCostThis) * 100.00 / $timesheetData->ForecastChargeThis,2,'.',',') : '-'}}%</td>
                                    <td class="text-center">{{ $timesheetData->ActualChargeThis != 0.00 ? number_format(($timesheetData->ActualChargeThis - $timesheetData->ActualCostThis) * 100.00 / $timesheetData->ActualChargeThis,2,'.',',') : '-' }}%</td>
                                    <td class="text-center">{{ $timesheetData->ForecastChargeFuture != 0.00 ? number_format(($timesheetData->ForecastChargeFuture - $timesheetData->ForecastCostFuture) * 100.00 / $timesheetData->ForecastChargeFuture,2,'.',',') : '-'}}%</td>
                                </tr>
                                @endif
                                @if(isset($pAndLData->PaLIncomeGBPPast) && isset($xeroData->IncomeGBPPast))
                                <tr>
                                    <td>Xero Margin</td>
                                    <td class="text-center">{{ $pAndLData->PaLIncomeGBPPast != 0.00 ? number_format(($pAndLData->PaLIncomeGBPPast - $xeroData->ExpenditureGBPPast - $pAndLData->PaLOverheadsGBPPast) * 100.00 / $pAndLData->PaLIncomeGBPPast,2,'.',',') : '-' }}%</td>
                                    <td></td>
                                    <td class="text-center">{{ $pAndLData->PaLIncomeGBPThis != 0.00 ? number_format(($pAndLData->PaLIncomeGBPThis - $xeroData->ExpenditureGBPThis - $pAndLData->PaLOverheadsGBPThis) * 100.00 / $pAndLData->PaLIncomeGBPThis,2,'.',',') : '-' }}%</td>
                                    <td class="text-center">{{ $pAndLData->PaLIncomeGBPFuture != 0.00 ? number_format(($pAndLData->PaLIncomeGBPFuture - $xeroData->ExpenditureGBPFuture - $pAndLData->PaLOverheadsGBPFuture) * 100.00 / $pAndLData->PaLIncomeGBPFuture,2,'.',',') : '-' }}%</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        // Clone project select
        projectClone = $('.ProjectSelect').clone();

        // Set Project subset first time through
        setProjects(true);

        // Show the update button when something changes
        $('.filter').change(function() {
            setProjects(false);
            $('#button_span').show();
        });
    });

    function setProjects(firsttime) {
        // Reinstate the Project options from the clone
        $('.ProjectSelect').find('option').remove();
        $('.ProjectSelect').append(projectClone.find('option').clone());

        // Works for one selection for now
        dealtype = $('.DealTypeOpt:selected').first().val();
        customer = $('.CustomerOpt:selected').first().val();
        onlyopen = $('#onlyopen:active').val();

        // Loop through the projects
        $('.ProjectSelect').find('option').each( function() {
            // If we have a dealtype/customer then remove Projects of other types
            dealshow = (typeof(dealtype) == 'undefined') || ($(this).attr('dealtype') == dealtype) || ($(this).attr('dealtype') == '*');
            custshow = (typeof(customer) == 'undefined') || ($(this).attr('customer') == customer) || ($(this).attr('customer') == '*');
            openshow = (typeof(onlyopen) == 'undefined') || (onlyopen == 'N') || ($(this).attr('live') == 'Y');
            show = dealshow && custshow && openshow;

            if (show) {
                if (!firsttime) {
                    $(this).prop('selected', false);
                }
            } else {
                $(this).remove();
            }
        });
    }
</script>
@endsection

