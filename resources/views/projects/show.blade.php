@extends('layouts.app')

@section('style')
@endsection

@section('viewName')
    Project Financial History
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h4>Project: <?php if (substr($project->Name,0,8) != $project->Code) echo $project->Code . '&nbsp'; ?>{{$project->Name}}</h4>
                <div class="panel panel-default">
                    <div class="panel-body">
                        <table id="project_history" class="table table-hover table-bordered table-striped" style="width:100%">
                            <thead>
                            <tr>
                                <th>Year</th>
                                <th>Month</th>
                                <th>Revenue</th>
                                <th>Expenditure</th>
                                <th>Overheads</th>
                                <th>Profit Month</th>
                                <th>Cum. Rev.</th>
                                <th>Cum. Exp.</th>
                                <th>Cum. Ovhd.</th>
                                <th>Cum. Profit</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php // Initialise
                            $year = 0;
                            $month = 0;
                            $revenue = 0.00;
                            $expenditure = 0.00;
                            $overheads = 0.00;
                            $cum_revenue = 0.00;
                            $cum_expenditure = 0.00;
                            $cum_overheads = 0.00;
                            ?>
                            @foreach($data as $datum)
                                <?php

                                    $cum_revenue += $datum->Revenue;
                                    $cum_expenditure += $datum->Costs;
                                    $cum_overheads += $datum->Overheads;
                                ?>
                                <tr>
                                    <td class="text-xs-center">{{$datum->Year}}</td>
                                    <td class="text-xs-center">{{$datum->Month}}</td>
                                    <td class="text-xs-right">{{number_format($datum->Revenue, 2, '.', ',')}}</td>
                                    <td class="text-xs-right">{{number_format($datum->Costs, 2, '.', ',')}}</td>
                                    <td class="text-xs-right">{{number_format($datum->Overheads, 2, '.', ',')}}</td>
                                    <td class="text-xs-right">{{number_format($datum->Revenue + $datum->Costs + $datum->Overheads, 2, '.', ',')}}</td>
                                    <td class="text-xs-right">{{number_format($cum_revenue, 2, '.', ',')}}</td>
                                    <td class="text-xs-right">{{number_format($cum_expenditure, 2, '.', ',')}}</td>
                                    <td class="text-xs-right">{{number_format($cum_overheads, 2, '.', ',')}}</td>
                                    <td class="text-xs-right">{{number_format($cum_revenue + $cum_expenditure + $cum_overheads, 2, '.', ',')}}</td>
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
    @include('lib.currency_field')
    <script type="text/javascript" defer>
        window.addEventListener('load', function () {
            oTable = $('#project_history').DataTable({
                searching: false,
                scrollY:        '100%',
                scrollCollapse: true,
                paging:         false,
                dom: 'frtiB',
                buttons: [ 'copy', 'excel', 'pdf' ],
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
