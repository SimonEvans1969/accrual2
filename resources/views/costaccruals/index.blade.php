@extends('layouts.app')

@section('style')
@endsection

@section('viewName')
Cost Accruals
@endsection

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-body">
					<form id="reload" action="/costaccruals" method="GET">
						As At:&nbsp;
						 <select class="browser-default custom-select reload" name="year" style="width: 150px">
        				<?php
            				$yr = 2020;
            				$curr_year = intval(date('Y'));
            				while ($yr <= $curr_year) {
                				$yr_select = ($yr == $selected_year) ? ' selected ' : '';
                				echo "<option $yr_select >$yr</option>";
                				$yr++;
							}
        				?>
    					</select>
    					&nbsp;/&nbsp;
    <select class="browser-default custom-select reload" name="month" style="width: 150px">
        <option value="01" <?php if ("{$selected_month}" == "01") echo " selected "; ?>>Jan</option>
        <option value="02" <?php if ("{$selected_month}" == "02") echo " selected "; ?>>Feb</option>
        <option value="03" <?php if ("{$selected_month}" == "03") echo " selected "; ?>>Mar</option>
        <option value="04" <?php if ("{$selected_month}" == "04") echo " selected "; ?>>Apr</option>
        <option value="05" <?php if ("{$selected_month}" == "05") echo " selected "; ?>>May</option>
        <option value="06" <?php if ("{$selected_month}" == "06") echo " selected "; ?>>Jun</option>
        <option value="07" <?php if ("{$selected_month}" == "07") echo " selected "; ?>>Jul</option>
        <option value="08" <?php if ("{$selected_month}" == "08") echo " selected "; ?>>Aug</option>
        <option value="09" <?php if ("{$selected_month}" == "09") echo " selected "; ?>>Sep</option>
        <option value="10" <?php if ("{$selected_month}" == "10") echo " selected "; ?>>Oct</option>
        <option value="11" <?php if ("{$selected_month}" == "11") echo " selected "; ?>>Nov</option>
        <option value="12" <?php if ("{$selected_month}" == "12") echo " selected "; ?>>Dec</option>
    </select>
					</form>
                    <table id="cost_accruals" class="table table-hover table-bordered table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th>Name</th>
								<th>Company</th>
								<th>Project</th>
                                <th>Type</th>
                                <th>Year</th>
                                <th>Month</th>
                                <th>Proposed Accrual</th>
								<th>Correction</th>
								<th>Agreed Accrual</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cost_accruals as $cost_accrual)
                                <tr assignment-id={{$cost_accrual->ID}}>
									<td data-item="Name" data-readonly="true">
										{{$cost_accrual->firstName}}&nbsp;{{$cost_accrual->lastName}}
									</td>
									<td data-item="Company" data-readonly="true">
                                        {{$cost_accrual->companyName}}
                                    </td>
                                    <td data-item="Project" data-readonly="true">
<?php if (substr($cost_accrual->Name, 0, strlen($cost_accrual->Code)) != $cost_accrual->Code) {
			echo $cost_accrual->Code;
			echo '&nbsp;:&nbsp;';
}
?>
{{$cost_accrual->Name}}
                                    </td>
									<td data-item="DealType" data-readonly="true">
                                        {{$cost_accrual->DealType}}
                                    </td>
                                    <td data-item="Year" data-readonly="true">
                                        {{$cost_accrual->Year}}
                                    </td>
                                    <td data-item="Month" data-readonly="true">
                                        {{$cost_accrual->Month}}
                                    </td>
									<td data-item="Cost" data-readonly="true">
                                        £{{number_format($cost_accrual->Cost,2)}}
                                    </td>
									<td title="{{ $cost_accrual->CorrectionComment }}" data-item="Correction">
                                        £{{number_format($cost_accrual->Correction,2)}}
                                    </td>
									<td title="{{ $cost_accrual->AccrualComment }}" data-item="Accrual"
				<?php if ($cost_accrual->Status == 'POSTED') echo ' data-readonly="true" '; ?>  >
                                        £{{number_format($cost_accrual->Accrual,2)}}
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
@include('costaccruals.modals.modal-costaccrual-update')
@endsection

@section('script')
@include('lib.currency_field')
<script type="text/javascript" defer>
window.addEventListener('load', function () {
	oTable = $('#cost_accruals').DataTable({
		searching: true,
        order: [[ 2, 'asc']],
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

	$('.reload').change(function(){
		$('#reload').submit();
	})

	$('#cost_accruals').find('tbody>tr').click(function(){
		$(this).find('td').each(function (){
			$('#costaccrualModal').find('#'+$(this).attr('data-item')).val($.trim($(this).text()));
			$('#costaccrualModal').find('#'+$(this).attr('data-item')).prop('readonly',
												($(this).attr('data-readonly') == "true"));
		});
		$(this).closest('tr').attr('assignment-id')
		$('#costaccrualModal').find('#AssignmentID').val($(this).closest('tr').attr('assignment-id'));
		$('#costaccrualModal').modal('show');
		return(false);
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
