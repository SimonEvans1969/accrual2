@extends('layouts.app')

@section('style')
@endsection

@section('viewName')
Xero Profit And Loss
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Xero Profit and Loss integration</div>

                <div class="card-body">
	@if(isset($warning))
		<div class="alert alert-info" role="alert">{{ $warning }}</div>
	@endif
	@if(isset($message))
		<div class="alert alert-info" role="alert">{{ $message }}</div>
	@endif
	<form action="/xero/getpl" method="get">
		<row>
		<select class="browser-default custom-select reload" name="year" style="width: 150px">
			<option disabled selected>Pick a year...</option>

        <?php
           $yr = 2020;
           $curr_year = intval(date('Y'));
           while ($yr <= $curr_year) {
               echo "<option>$yr</option>";
                				$yr++;
		   }
       	?>
    	</select>
    	&nbsp;/&nbsp;
        <select class="browser-default custom-select reload" name="month" style="width: 150px">
            <option disabled selected>Pick a month...</option>
            <option value="01" >Jan</option>
            <option value="02" >Feb</option>
            <option value="03" >Mar</option>
            <option value="04" >Apr</option>
            <option value="05" >May</option>
            <option value="06" >Jun</option>
            <option value="07" >Jul</option>
            <option value="08" >Aug</option>
            <option value="09" >Sep</option>
            <option value="10" >Oct</option>
            <option value="11" >Nov</option>
            <option value="12" >Dec</option>
        </select>
		</row>
		<row>
			<button type="submit" class="btn btn-success btn-save col-md-4">Load Xero P&amp;L</button>
		</row>
	</form>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

@section('script')
@endsection
