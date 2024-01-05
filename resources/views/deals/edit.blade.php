@extends('layouts.app')

@section('style')
@endsection

@section('viewName')
{{$mode == 'CREATE' ? 'Add' : 'Update'}} Deal
@endsection

@section('content')
<div class="container">
	<form id="dealsform" action="{{$mode == 'CREATE' ? '/deals' : '/deals/' . $deal->Id }}"
		  method="post" class="needs-validation">
    {!! csrf_field() !!}
	@if ($mode != 'CREATE')
		{{ method_field('put') }}
	@endif
	<div class="row">
        <div class="col">
            <div class="form-group has-feedback row {{ $errors->has('CustomerId') ? ' has-error ' : '' }}">
                <label for="CustomerId" class="col-md-2 control-label" style="margin-bottom: 0px; align-self: center">Customer:</label>
                <div class="col-md-9">
                    <div class="input-group" >
                        <select class="browser-default custom-select select-save" id="CustomerId" name="CustomerId">
							@if( ! $deal->ContactId )
							<option disabled selected id="_disabled_CustomerOption">Select Customer...</option>
							@endif
							<option value="_New_" id="_New_CustomerOption">Add New Customer...</option>
                            <?php foreach($customers as $customer) { ?>
                            <option value="{{$customer->ID}}"
                                    <? if ($deal->CustomerId == $customer->ID) echo ' selected '; ?>
                                        >{{$customer->Name}}</option>
                            <?php } ?>
                        </select>
                        <div class="input-group-append">
                            <label class="input-group-text" for="CustomerId">
                                <i class="fas fa-fw fa-hospital" aria-hidden="true"></i>
                            </label>
                        </div>
                    </div>
                    @if ($errors->has('CustomerId'))
                    <span class="help-block .text-danger">
                        <strong>{{ $errors->first('CustomerId') }}</strong>
                    </span>
                    @endif
                </div>
            </div>

            <div class="form-group has-feedback row {{ $errors->has('Name') ? ' has-error ' : '' }}">
                <label for="Name" class="col-md-2 control-label" style="margin-bottom: 0px; align-self: center">Deal name:</label>
                <div class="col-md-9">
                    <div class="input-group" >
                        <input type="text" class="form-control" id="Name" name="Name" value="{{$deal->Name}}">
                        <div class="input-group-append">
                            <label class="input-group-text" for="Name">
                                <i class="fas fa-fw fa-asterisk" aria-hidden="true"></i>
                            </label>
                        </div>
                    </div>
                    @if ($errors->has('Name'))
                    <span class="help-block .text-danger">
                        <strong>{{ $errors->first('Name') }}</strong>
                    </span>
                    @endif
                </div>
            </div>

            <div class="form-group has-feedback row {{ $errors->has('Description') ? ' has-error ' : '' }}">
                <label for="Name" class="col-md-2 control-label" style="margin-bottom: 0px; align-self: center">Description:</label>
                <div class="col-md-9">
                    <div class="input-group" >
                        <textarea class="form-control" id="Description" name="Description" rows="4">{{$deal->Description}}</textarea>
                        <div class="input-group-append">
                            <label class="input-group-text" for="Description">
                                <i class="fas fa-fw fa-question-circle" aria-hidden="true"></i>
                            </label>
                        </div>
                    </div>
                    @if ($errors->has('Description'))
                    <span class="help-block .text-danger">
                        <strong>{{ $errors->first('Description') }}</strong>
                    </span>
                    @endif
                </div>
            </div>

            <div class="form-group has-feedback row {{ $errors->has('Amount') ? ' has-error ' : '' }}">
                <label for="Amount" class="col-md-2 control-label" style="margin-bottom: 0px; align-self: center">Deal Value:</label>
                <div class="col-md-9">
                    <div class="input-group" >
                        <input type="text" class="form-control" id="Amount" value="{{$deal->Amount}}" data-type="currency" name="Amount">
                        <div class="input-group-append">
                            <label class="input-group-text" for="Amount">
                                <i class="fas fa-fw fa-pound-sign" aria-hidden="true"></i>
                            </label>
                        </div>
                    </div>
                    @if ($errors->has('Amount'))
                    <span class="help-block .text-danger">
                        <strong>{{ $errors->first('Amount') }}</strong>
                    </span>
                    @endif
                </div>
            </div>

			<div class="form-group has-feedback row {{ $errors->has('CurrentYearRevenueAllocation') ? ' has-error ' : '' }}">
                <label for="CurrentYearRevenueAllocation" class="col-md-2 control-label" style="margin-bottom: 0px; align-self: center">Current year revenue:</label>
                <div class="col-md-9">
                    <div class="input-group" >
                        <input type="text" class="form-control" id="CurrentYearRevenueAllocation" name="CurrentYearRevenueAllocation"
                            value="{{$deal->CurrentYearRevenueAllocation}}" data-type="currency">
                        <div class="input-group-append">
                            <label class="input-group-text" for="CurrentYearRevenueAllocation">
                                <i class="fas fa-fw fa-pound-sign" aria-hidden="true"></i>
                            </label>
                        </div>
                    </div>
                    @if ($errors->has('CurrentYearRevenueAllocation'))
                    <span class="help-block .text-danger">
                        <strong>{{ $errors->first('CurrentYearRevenueAllocation') }}</strong>
                    </span>
                    @endif
                </div>
            </div>

            <div class="form-group has-feedback row {{ $errors->has('CloseDate') ?' has-error ' : '' }}">
                <label for="CloseDate" class="col-md-2 control-label"style="margin-bottom: 0px; align-self: center">Close Date:</label>
                <div class="col-md-9">
                    <div class="input-group date">
                        <input type="text" class="form-control datepicker" id="CloseDate" name="CloseDate"
                               value="{{$deal->CloseDate ? $deal->CloseDate->format('d/m/Y') : ''}}">
                        <div class="input-group-addon">
                            <span class="glyphicon glyphicon-th"></span>
                        </div>
                    </div>
                    @if ($errors->has('CloseDate'))
                    <span class="help-block .text-danger">
                        <strong>{{ $errors->first('CloseDate') }}</strong>
                    </span>
                    @endif
                </div>
            </div>
        </div>
        <div class="col">
            <div class="form-group has-feedback row {{ $errors->has('DealTypeId') ? ' has-error ' : '' }}">
                <label for="DealTypeId" class="col-md-2 control-label" style="margin-bottom: 0px; align-self: center">Type:</label>
                <div class="col-md-9">
                    <div class="input-group" >
                        <select class="browser-default custom-select select-save" id="DealTypeId" name="DealTypeId">
                            <option disabled selected >Select Deal type...</option>
                            <?php foreach($dealTypes as $dealType) { ?>
                            <option value="{{$dealType->Id}}"
                                    <? if ($deal->DealTypeId == $dealType->Id) echo ' selected '; ?>
                                        >{{$dealType->Description}}</option>
                            <?php } ?>
                        </select>
                        <div class="input-group-append">
                            <label class="input-group-text" for="DealTypeId">
                                <i class="fas fa-fw fa-asterisk" aria-hidden="true"></i>
                            </label>
                        </div>
                    </div>
                    @if ($errors->has('DealTypeId'))
                    <span class="help-block .text-danger">
                        <strong>{{ $errors->first('DealTypeId') }}</strong>
                    </span>
                    @endif
                </div>
            </div>

			<div class="form-group has-feedback row {{ $errors->has('DealSubTypes_id') ? ' has-error ' : '' }}">
                <label for="DealSubTypes_id" class="col-md-2 control-label" style="margin-bottom: 0px; align-self: center">Sub-type:</label>
                <div class="col-md-9">
                    <div class="input-group" >
                        <select class="browser-default custom-select select-save" id="DealSubTypes_id" name="DealSubTypes_id">
                            <option disabled selected id="DealSubType_first" >Select Deal sub-type...</option>
                            <?php foreach($dealSubTypes as $dealSubType) { ?>
                            <option value="{{$dealSubType->Id}}" deal-type="{{$dealSubType->DealTypeId}}" class="deal-sub-type"
                                    <? if ($deal->DealSubTypes_id == $dealSubType->Id) echo ' selected '; ?>
                                        >{{$dealSubType->Description}}</option>
                            <?php } ?>
                        </select>
                        <div class="input-group-append">
                            <label class="input-group-text" for="DealSubTypes_id">
                                <i class="fas fa-fw fa-asterisk" aria-hidden="true"></i>
                            </label>
                        </div>
                    </div>
                    @if ($errors->has('DealSubTypes_id'))
                    <span class="help-block .text-danger">
                        <strong>{{ $errors->first('DealSubTypes_id') }}</strong>
                    </span>
                    @endif
                </div>
            </div>

            <div class="form-group has-feedback row {{ $errors->has('DealStageId') ? ' has-error ' : '' }}">
                <label for="DealStageId" class="col-md-2 control-label" style="margin-bottom: 0px; align-self: center">Stage:</label>
                <div class="col-md-9">
                    <div class="input-group" >
                        <select class="browser-default custom-select select-save" id="DealStageId" name="DealStageId">
                            <option disabled selected >Select stage...</option>
                            <?php foreach($dealStages as $dealStage) { ?>
                            <option value="{{$dealStage->Id}}"
                                    <? if ($deal->DealStageId == $dealStage->Id) echo ' selected '; ?>
                                        >{{$dealStage->Description}}</option>
                            <?php } ?>
                        </select>
                        <div class="input-group-append">
                            <label class="input-group-text" for="DealStageId">
                                <i class="fas fa-fw fa-asterisk" aria-hidden="true"></i>
                            </label>
                        </div>
                    </div>
                    @if ($errors->has('DealStageId'))
                    <span class="help-block .text-danger">
                        <strong>{{ $errors->first('DealStageId') }}</strong>
                    </span>
                    @endif
                </div>
            </div>

            <div class="form-group has-feedback row {{ $errors->has('PipelineId') ? ' has-error ' : '' }}">
                <label for="PipelineId" class="col-md-2 control-label" style="margin-bottom: 0px; align-self: center">Competitive</label>
                <div class="col-md-9">
                    <input type="checkbox" data-toggle="toggle" data-onstyle="warning" data-offstyle="info" data-on="Yes" data-off="No" id="PipelineId" name="PipelineId"
                    <?php if ("{$deal->PipelineId}" == "1" ) echo " checked "; ?> >
                    @if ($errors->has('PipelineId'))
                        <span class="help-block .text-danger">
                            <strong>{{ $errors->first('PipelineId') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group has-feedback row {{ $errors->has('MustWin') ? ' has-error ' : '' }}">
                <label for="MustWin" class="col-md-2 control-label" style="margin-bottom: 0px; align-self: center">Must Win</label>
                <div class="col-md-9">
                    <input type="checkbox" data-toggle="toggle" data-onstyle="danger" data-offstyle="info" data-on="Yes"
						   data-off="No" id="MustWin" name="MustWin"
                    <?php if ("{$deal->MustWin}" == "1" ) echo " checked "; ?> >
                    @if ($errors->has('MustWin'))
                        <span class="help-block .text-danger">
                            <strong>{{ $errors->first('MustWin') }}</strong>
                        </span>
                    @endif
                </div>
			</div>

            <div class="form-group has-feedback row {{ $errors->has('DealSourceId') ? ' has-error ' : '' }}">
                <label for="DealSourceId" class="col-md-2 control-label" style="margin-bottom: 0px; align-self: center">Source:</label>
                <div class="col-md-9">
                    <div class="input-group" >
                        <select class="browser-default custom-select select-save" id="DealSourceId" name="DealSourceId">
                            <option disabled selected >Select source...</option>
                            <?php foreach($dealSources as $dealSource) { ?>
                            <option value="{{$dealSource->Id}}"
                            <? if ($deal->DealSourceId == $dealSource->Id) echo ' selected '; ?>
                            >{{$dealSource->Description}}</option>
                            <?php } ?>
                        </select>
                        <div class="input-group-append">
                            <label class="input-group-text" for="DealSourceId">
                                <i class="fas fa-fw fa-asterisk" aria-hidden="true"></i>
                            </label>
                        </div>
                    </div>
                    @if ($errors->has('DealSourceId'))
                        <span class="help-block .text-danger">
                        <strong>{{ $errors->first('DealSourceId') }}</strong>
                    </span>
                    @endif
                </div>
            </div>

            <div class="form-group has-feedback row {{ $errors->has('OwnerId') ? ' has-error ' : '' }}">
                <label for="OwnerId" class="col-md-2 control-label" style="margin-bottom: 0px; align-self: center">Owner:</label>
                <div class="col-md-9">
                    <div class="input-group" >
                        <select class="browser-default custom-select select-save" id="OwnerId" name="OwnerId">
                            <option disabled selected >Select Owner...</option>
                            <?php foreach($owners as $owner) { ?>
                            <option value="{{$owner->Id}}"
                                    <? if ($deal->OwnerId == $owner->Id) echo ' selected '; ?>
                                        >{{$owner->firstName}} {{$owner->lastName}}</option>
                            <?php } ?>
                        </select>
                        <div class="input-group-append">
                            <label class="input-group-text" for="OwnerId">
                                <i class="fas fa-fw fa-asterisk" aria-hidden="true"></i>
                            </label>
                        </div>
                    </div>
                    @if ($errors->has('OwnerId'))
                    <span class="help-block .text-danger">
                        <strong>{{ $errors->first('OwnerId') }}</strong>
                    </span>
                    @endif
                </div>
            </div>

            <div class="form-group has-feedback row {{ $errors->has('ContactId') ? ' has-error ' : '' }}">
                <label for="ContactId" class="col-md-2 control-label" style="margin-bottom: 0px; align-self: center">Contact:</label>
                <div class="col-md-9">
                    <div class="input-group" >
                        <select class="browser-default custom-select select-save" id="ContactId" name="ContactId">
                            @if( ! $deal->ContactId )
							<option disabled selected id="_disabled_ContactOption">Select Contact...</option>
							@endif
							<option value="_New_" id="_New_ContactOption">Add New Contact...</option>
							<optgroup label="Contacts for this customer" id="thisCustomerOptions">
							</optgroup>
							<optgroup label="All contacts" id="allCustomerOptions">
                                <?php foreach($contacts as $contact) { ?>
                                <option value="{{$contact->Id}}" customer="|{{$contact->Customers}}|"
                                        <? if ($deal->ContactId == $contact->Id) echo ' selected '; ?>
                                            >{{$contact->FirstName}} {{$contact->LastName}}</option>
                                <?php } ?>
							</optgroup>
                        </select>
                        <div class="input-group-append">
                            <label class="input-group-text" for="ContactId">
                                <i class="fas fa-fw fa-asterisk" aria-hidden="true"></i>
                            </label>
                        </div>
                    </div>
                    @if ($errors->has('ContactId'))
                    <span class="help-block .text-danger">
                        <strong>{{ $errors->first('ContactId') }}</strong>
                    </span>
                    @endif
                </div>
            </div>
        </div>
	</div>
	<div class="row">
		<div class="col-md-6">
			<button type="submit" class="btn btn-success btn-save col-md-4">Save Record</button>
			<button id="deleteButton" class="btn btn-danger col-md-4 hidden">Delete Record</button>
		</div>
		<div class="col-md-6">
			@if ($mode != 'CREATE')
				<small>
					Created by {{$createdByName}} at {{($deal->CreatedDate ? $deal->CreatedDate->format('d/m/y H:i:s') : '')}}
					<br/>
					Last Updated by {{$lastUpdatedByName}} at {{($deal->LastUpdatedDate ? $deal->LastUpdatedDate->format('d/m/y H:i:s') : '')}}
				</small>
			@endif
		</div>
	</div>
	<div class="row">

	</div>
	</form>
</div>
@if ($mode != 'CREATE')
	@include('deals.modals.modal-delete')
@endif
@include('deals.modals.modal-contact-create')
@include('deals.modals.modal-customer-create')
@endsection

@section('script')
@include('lib.currency_field')
<script type="text/javascript" defer>
window.addEventListener('load', function () {
	// Handle dates
	$(".datepicker").datepicker(
    	{
        	locale: 'en-gb',
            format: 'dd/mm/yyyy',
        }
     );


@if ($mode != 'CREATE')
	$('#deleteButton').click(function() {
		$('#deleteConfirmForm').attr('action','/deals/{{$deal->Id}}');
		$('#confirmDeleteModal').modal('show');
		return(false);
	});

	$('#DealStageId').change(function() {
		setDeleteButton();
	});

	setDeleteButton();

	function setDeleteButton() {
		if ($('#DealStageId').val() == '8')
				$('#deleteButton').show();
			else
				$('#deleteButton').hide();
	}

@endif

	$('#ContactId').change(function() {
		if ($(this).val() == '_New_') {
			// Clear values
			$("#createContactModal").find(".form-select").each(function() {
				$(this).val('');
				$(this).find('option:selected').each(function(){
					$(this).removeAttr('selected');
				});
			});
			// Set the ContactCustomerID
			$('#ContactCustomerID').val($('#CustomerId').val());
			// Show the modal
			$("#createContactModal").modal('show');
			return(false);
		}
	});

	$('#CustomerId').change(function() {
		setContactOptions();
		if ($(this).val() == '_New_') {
			// Clear values
			$("#createCustomerModal").find(".form-select").each(function() {
				$(this).val('');
				$(this).find('option:selected').each(function(){
					$(this).removeAttr('selected');
				});
			});
			// Show the modal
			$("#createCustomerModal").modal('show');
			return(false);
		}
	});

	$('#DealTypeId').change(function() {
		setDealSubTypes();
	});

	dealSubTypes = $('.deal-sub-type').clone();
	setDealSubTypes();

	setContactOptions();

	function setContactOptions() {
		// Remove old options
		$('#thisCustomerOptions').empty();

		// Build list of new options
		customer = $('#CustomerId').val();

		if (customer) {
            $('#allCustomerOptions').children().each( function() {
                if ($(this).attr('customer').includes('|' + customer + '|')) {
                    $('#thisCustomerOptions').append($(this).clone());
                }
            });
		}
	}

	function setDealSubTypes() {
		// Empty the options
		$('.deal-sub-type').remove();

		// Put back any applicable options
		dealSubTypes.each( function() {
			if ($(this).attr('deal-type') == $('#DealTypeId').val())
				$('#DealSubTypes_id').append($(this).clone());
		});
	};

});
</script>
@endsection
