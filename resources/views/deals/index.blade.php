@extends('layouts.app')

@section('style')
@endsection

@section('viewName')
Deals
@endsection

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    <table id="deals" class="table table-hover table-bordered stripe">
                        <thead>
                            <tr>
								<th>Customer</th>
								<th>Name</th>
								<th>Description</th>
                                <th>Stage</th>
								<th>Type</th>
                                <th>Value</th>
                                <th>Close Date</th>
								<th>Owner</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $sumTotal = 0; $sumPipeline = 0; ?>
                            @foreach($deals as $deal)
                                <tr deal="{{$deal->Id}}">
                                    <td>
                                        {{ $deal->Customer }}
                                    </td>
									<td>
                                        {{ $deal->Name }}
                                    </td>
									<td>
                                        {{ $deal->Description }}
                                    </td>
                                    <td>
                                        {{ $deal->DealStage }}
                                    </td>
									<td>
                                        {{ $deal->DealType }}
                                    </td>
									<td class="text-right">
                                        £{{ number_format($deal->Amount) }}
                                        <?php $sumTotal += $deal->Amount;
											  $sumPipeline += ($deal->Amount * $deal->Percentage); ?>
                                    </td>
                                    <td data-order="{{ $deal->CloseDate ? $deal->CloseDate->format('Y-m-d') : '-' }}">
                                        {{ $deal->CloseDate ? $deal->CloseDate->format('d/m/Y') : '-' }}
                                    </td>
									<td>
                                        {{ $deal->DealOwner }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
						<tfoot>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td class="text-right">£{{ number_format($sumPipeline) }}</td>
							<td></td>
							<td></td>
						</tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<form id="filters" action="deals" method="get" >
    <div class="row mb-2">
    <div class="btn-toolbar col-md-11" role="toolbar" aria-label="Filter toolbar">
        <div class="btn-group" role="group" aria-label="Filters">
            <button type="submit" name="filter" class="btn btn-secondary {{ $filter == 'My' ? 'active' : '' }}" value="My">My Deals</button>
            <button type="submit" name="filter" class="btn btn-secondary {{ $filter == 'New' ? 'active' : '' }}" value="New">New Deals</button>
			<button type="submit" name="filter" class="btn btn-secondary {{ $filter == 'Overdue' ? 'active' : '' }}" value="Overdue">Overdue</button>
            <button type="submit" name="filter" class="btn btn-secondary {{ $filter == 'This' ? 'active' : '' }}" value="This">This month</button>
            <button type="submit" name="filter" class="btn btn-secondary {{ $filter == 'Next' ? 'active' : '' }}" value="Next">Next month</button>
            <button type="submit" name="filter" class="btn btn-secondary {{ $filter == 'All' ? 'active' : '' }}" value="All">All Open</button>
            <button type="submit" name="filter" class="btn btn-secondary {{ $filter == 'Recent' ? 'active' : '' }}" value="Recent">Recently Closed</button>
			<button type="submit" name="filter" class="btn btn-secondary {{ $filter == 'WonYr' ? 'active' : '' }}" value="WonYr">Won this Year</button>
			<button type="submit" name="filter" class="btn btn-secondary {{ $filter == 'LostYr' ? 'active' : '' }}" value="LostYr">Lost this Year</button>
        </div>
    </div>
    <a href="/deals/create" class="btn btn-primary active pull-right col-md-1" role="button" aria-pressed="true">New deal</a>
    </div>
</form>
@endsection

@section('script')
<script type="text/javascript" defer>
window.addEventListener('load', function () {
	oTable = $('#deals').DataTable({
		searching:      true,
        scrollY:        '100%',
        scrollCollapse: true,
        paging:         false,
	});

    $('#filters').prependTo($('#deals_wrapper'));

	setTableHeight();
	$(window).resize(function(){
		setTableHeight();
	});

    $('#deals tbody').on('click', 'tr', function () {
        var deal_id = $(this).attr('deal');
		window.location.href = "{{url('deals')}}/" + deal_id + "/edit";
    } );

});

function setTableHeight() {
	var otherHeight = $('body').height() - $('.dataTables_scrollBody').height();
	var tableHeight = $(window).height() - otherHeight - 1;
	tableHeight = Math.max(200, tableHeight);
	$('.dataTables_scrollBody').css('height', tableHeight + 'px');
}

</script>
@endsection
