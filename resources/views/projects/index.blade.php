@extends('layouts.app')

@section('style')
@endsection

@section('viewName')
Projects
@endsection

@section('content')
<div class="container">
    <div class="col-md-3">
        <form id="filters" action="projects" method="get" >
            <div class="btn-toolbar" role="toolbar" aria-label="Filter toolbar">
                <div class="btn-group" role="group" aria-label="Filters">
                    <button type="submit" name="filter" class="btn btn-secondary {{ $filter != 'All' ? 'active' : '' }}" value="Live">Live Projects</button>
                    <button type="submit" name="filter" class="btn btn-secondary {{ $filter == 'All' ? 'active' : '' }}" value="All">All Projects</button>
                </div>
            </div>
        </form>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    <table id="projects" class="table table-hover table-bordered table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th>ID</th>
								<th>Customer</th>
								<th>Code</th>
                                <th>Name</th>
                                <th>Start</th>
                                <th>End</th>
                                <th>Project Manager</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($projects as $project)
                                <tr>
									<td>
										{{ $project->ID }}
									</td>
                                    <td>
                                        {{ $project->Customer }}
                                    </td>
									<td>
                                        {{ $project->Code }}
                                    </td>
                                    <td>
                                        {{ $project->Name }}
                                    </td>
                                    <td>
                                        {{ $project->StartDate->format('d/m/Y') }}
                                    </td>
                                    <td>
                                        {{ $project->EndDate->format('d/m/Y') }}
                                    </td>
									<td>
                                        {{ $project->firstName }}&nbsp;{{ $project->lastName }}
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
	oTable = $('#projects').DataTable({
		searching: true,
        order: [[ 2, 'asc']],
        scrollY:        '100%',
        scrollCollapse: true,
        paging:         false,
		columnDefs: [ {
      		targets: 0,
      		searchable: false,
			visible: false
    		} ]
	});
	setTableHeight();
	$(window).resize(function(){
		setTableHeight();
	});

	$('#projects').on('click', 'tr', function () {
        var project_id = oTable.row( this ).data()[0];
		window.location.href = "{{url('accrual')}}/" + project_id;
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
