@extends('layouts.app')

@section('style')
@endsection

@section('viewName')
Deals
@endsection

@section('content')
<div class="container">
	<form id="filters" action="dealschart" method="get" >
    	<div class="row mb-2">
            <div class="col-md-8">
    		    <div class="btn-toolbar" role="toolbar" aria-label="Filter toolbar">
        		    <div class="btn-group" role="group" aria-label="Filters">
            		    <button type="submit" name="filter" class="btn btn-secondary {{ $filter == 'All' ? 'active' : '' }}" value="All">All Types</button>
					    @foreach($dealTypes as $dealType)
            			    <button type="submit" name="filter" class="btn btn-secondary {{ $filter == $dealType->Id ? 'active' : '' }}" value="{{$dealType->Id}}">{{$dealType->Description}}</button>
					    @endforeach
        		    </div>
    		    </div>
            </div>
            <div class="col-md-4">
                <div class="btn-toolbar float-right" role="toolbar" aria-label="Filter toolbar">
                    <div class="btn-group" role="group" aria-label="Filters">
                        <button type="submit" name="year" class="btn btn-secondary {{ $year == 'last' ? 'active' : '' }}" value="last">Last year</button>
                        <button type="submit" name="year" class="btn btn-secondary {{ $year == 'this' ? 'active' : '' }}" value="this">This year</button>
                    </div>
                </div>
            </div>
    	</div>
	</form>
    <div class="row">
        <div class="col-md-12">
			<canvas id="dealsChart"></canvas>
        </div>
    </div>
</div>
@endsection

@section('script')
<script type="text/javascript" defer>
window.addEventListener('load', function () {
	var ctx = document.getElementById('dealsChart');
	var myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [<?php
                        $first_item = true;
                        foreach ($data as $datum)
                        {
                            echo ($first_item ? '' : ' , ') . "'" . addslashes($datum['asAtDate']) . "'";
                            $first_item = false;
                        }
                    ?>],
            datasets: [
                {
                label: 'Pipeline',
                data: [
                    <?php
                        $first_item = true;
                        foreach ($data as $datum)
                        {
                            if (isset($datum['pipeline'])) echo ($first_item ? '' : ' , ') . $datum['pipeline'];
                            $first_item = false;
                        }
                    ?>
                ],
                type: 'line',
                fill: 'false',
				order: 5,
                borderColor: 'red',
                lineTension: 0,
                yAxisID: 'only-y-axis',
                }, {
                label: 'In-year Pipeline',
                data: [
                    <?php
                        $first_item = true;
                        foreach ($data as $datum)
                        {
                            if (isset($datum['inYearPipeline'])) {
                                echo ($first_item ? '' : ' , ') . $datum['inYearPipeline'];
                            }
                            $first_item = false;
                        }
                    ?>
                ],
                type: 'line',
                fill: 'false',
				order: 4,
                borderColor: 'pink',
                lineTension: 0,
                yAxisID: 'only-y-axis',
                },{
                label: 'Last Year Closed Won',
                data: [
                    <?php
                        $first_item = true;
                        foreach ($data as $datum)
                        {
                            if (isset($datum['LYclosedWon'])) {
                                echo ($first_item ? '' : ' , ') . $datum['LYclosedWon'];
                            }
                            $first_item = false;
                        }
                    ?>
                ],
                type: 'line',
                fill: 'false',
				order: 3,
                borderColor: 'purple',
                lineTension: 0,
                yAxisID: 'only-y-axis',
                },{
				label: 'Closed Won',
                data: [
                    <?php
                        $first_item = true;
                        foreach ($data as $datum)
                        {
                            if (isset($datum['closedWon'])) {
                                echo ($first_item ? '' : ' , ') . $datum['closedWon'];
                            }
                            $first_item = false;
                        }
                    ?>
                ],
                type: 'line',
				fill: true,
				order: 2,
                borderColor: 'DarkBlue',
                backgroundColor: 'LightBlue',
                lineTension: 0,
                yAxisID: 'only-y-axis',
				},{
				label: 'In-year Closed Won',
                data: [
                    <?php
                        $first_item = true;
                        foreach ($data as $datum)
                        {
                            if (isset($datum['inYearClosedWon'])) {
                                echo ($first_item ? '' : ' , ') . $datum['inYearClosedWon'];
                            }
                            $first_item = false;
                        }
                    ?>
                ],
                type: 'line',
				fill: true,
				order: 1,
                borderColor: 'DarkGreen',
                backgroundColor: 'LightGreen',
                lineTension: 0,
                yAxisID: 'only-y-axis',
				},
            ],
        },
        options: {
            scales: {
                yAxes: [{
                    id: 'only-y-axis',
                    type: 'linear',
                    ticks: {
                        min: 0,
                    }
                }]
            }
        }
    });
});

</script>
@endsection
