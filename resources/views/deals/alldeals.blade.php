@extends('layouts.app')

@section('style')
@endsection

@section('viewName')
All Deals
@endsection

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    <table id="deals" class="table table-hover table-bordered">
                        <thead>
                            <tr>
								<th>Name</th>
								<th>Description</th>
                                <th>Stage</th>
                                <th>Pipeline</th>
								<th>Type</th>
                                <th>Value</th>
								<th>Must Win</th>
                                <th>Close Date</th>
								<th>Closed Reason</th>
								<th>Current Year Value</th>
								<th>Owner</th>
								<th>Contact</th>
								<th>Created By</th>
								<th>Created Date</th>
								<th>Last Updated By</th>
								<th>Last Updated Date</th>
								<th>History</th>
								<th>Note</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($deals as $deal)
                                <tr deal="{{$deal->Id}}">
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
                                        {{ $deal->Pipeline }}
                                    </td>
									<td> 
                                        {{ $deal->DealType }}
                                    </td>
									<td> 
                                        {{ $deal->Amount }}
                                    </td>
									<td> 
                                        {{ $deal->MustWin ? 'YES' : '' }}
                                    </td>
                                    <td> 
                                        {{ $deal->CloseDate }}
                                    </td>
                                    <td> 
                                       {{ $deal->ClosedCancelledReason ?: $deal->ClosedLostReason ?: $deal->ClosedWonReason ?: '-'}}
                                    </td>
									<td> 
                                        {{ $deal->CurrentYearRevenueAllocation }}
                                    </td>
									<td> 
                                        {{ $deal->DealOwner }}
                                    </td>
									<td> 
                                        {{ $deal->Contact }}
                                    </td>
									<td> 
                                        {{ $deal->CreatedBy }}
                                    </td>
									<td> 
                                        {{ $deal->CreatedDate }}
                                    </td>
									<td> 
                                        {{ $deal->LastUpdatedBy }}
                                    </td>
									<td> 
                                        {{ $deal->LastUpdatedDate }}
                                    </td>
									<td> 
                                        {{ $deal->History }}
                                    </td>
									<td> 
                                        {{ $deal->Note }}
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
	oTable = $('#deals').DataTable({
		searching: true,
        order: [[ 1, 'asc']],
        scrollY:        '100%',
        scrollCollapse: true,
		scrollX:		true,
        paging:         false,
		buttons: 		[ 
							{
            					extend: 'excel',
            					text: 'Export to Excel'
        					},
						],
		dom: 			'frtiB',
		fixedCoumns:	true,
	});
	
	oTable.buttons().container()
        .appendTo( '#deals_wrapper .col-md-6:eq(0)' );
	
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
