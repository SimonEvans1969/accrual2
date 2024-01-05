@extends('layouts.app')

@section('style')
@endsection

@section('viewName')
Xero
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Xero integration</div>

                <div class="card-body">
@if($errors->any())
    <div class="alert alert-danger" role="alert">Your connection to Xero failed</div>
    <div class="alert alert-warning" role="alert">{{ $errors->first() }}</div>
    <a href="{{ route('xero.auth') }}" class="btn btn-primary btn-large mt-4">
        Reconnect to Xero
    </a>
@elseif($connected)
	<div class="alert alert-success" role="alert">You are connected to Xero</div>
	@if($warning)
		<div class="alert alert-info" role="alert">{{ $warning }}</div>
	@endif
	@if($message)
		<div class="alert alert-info" role="alert">{{ $message }}</div>
	@endif
	<a href="{{ route('xero.get') }}" class="btn btn-primary btn-large mt-4">
        Refresh Xero data feed
    </a>
@else
	<div class="alert alert-warning" role="alert">You are not connected to Xero</div>
    <a href="{{ route('xero.auth') }}" class="btn btn-primary btn-large mt-4">
        Connect to Xero
    </a>
@endif
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

@section('script')
@endsection
