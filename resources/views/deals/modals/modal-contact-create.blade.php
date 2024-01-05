<div class="modal fade modal-primary" id="createContactModal" role="dialog" aria-labelledby="createContactLabel" aria-hidden="true" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Create Contact
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    <span class="sr-only">close</span>
                </button>
            </div>
            <div class="modal-body">
				<div class="form-group has-feedback row">
                	<label for="TitleId" class="col-md-3 control-label">Title</label>
                   	<div class="col-md-9">
                    	<div class="input-group">
                        	<select class="browser-default custom-select form-control" name="TitleId" id="TitleId">
								<option disabled selected >Select Title...</option>
								@foreach( $titles as $title )
									<option value="{{$title->Id}}">{{$title->Value}}</option>
								@endforeach
                            </select>
                            <div class="input-group-append">
                            	<label class="input-group-text" for="TitleId">
                                	<i class="far fa-id-badge " aria-hidden="true"></i>
   			 					</label>
                            </div>
                      	</div>
                        <span class="help-block" error-target="TitleId"></span>
                  	</div>
             	</div>
				<div class="form-group has-feedback row">
                	<label for="FirstName" class="col-md-3 control-label">First Name</label>
                   	<div class="col-md-9">
                    	<div class="input-group">
                        	<input id="FirstName" class="form-control" placeholder="First Name" name="FirstName" type="text">
                            <div class="input-group-append">
                            	<label class="input-group-text" for="FirstName">
                                	<i class="far fa-id-badge " aria-hidden="true"></i>
   			 					</label>
                            </div>
                      	</div>
                        <span class="help-block" error-target="FirstName"></span>
                  	</div>
             	</div>
				<div class="form-group has-feedback row">
                	<label for="LastName" class="col-md-3 control-label">Last Name</label>
                   	<div class="col-md-9">
                    	<div class="input-group">
                        	<input id="LastName" class="form-control" placeholder="Last Name" name="LastName" type="text">
                            <div class="input-group-append">
                            	<label class="input-group-text" for="LastName">
                                	<i class="far fa-id-badge " aria-hidden="true"></i>
   			 					</label>
                            </div>
                      	</div>
                        <span class="help-block" error-target="LastName"></span>
                  	</div>
             	</div>
				<div class="form-group has-feedback row">
                	<label for="Email" class="col-md-3 control-label">Email</label>
                   	<div class="col-md-9">
                    	<div class="input-group">
                        	<input id="Email" class="form-control" placeholder="Email" name="Email" type="text">
                            <div class="input-group-append">
                            	<label class="input-group-text" for="Email">
                                	<i class="far fa-envelope" aria-hidden="true"></i>
   			 					</label>
                            </div>
                      	</div>
                        <span class="help-block" error-target="Email"></span>
                  	</div>
             	</div>
				<div class="form-group has-feedback row">
                	<label for="WorkPhone" class="col-md-3 control-label">Phone</label>
                   	<div class="col-md-9">
                    	<div class="input-group">
                        	<input id="WorkPhone" class="form-control" placeholder="Phone" name="WorkPhone" type="text">
                            <div class="input-group-append">
                            	<label class="input-group-text" for="WorkPhone">
                                	<i class="fas fa-mobile-alt" aria-hidden="true"></i>
   			 					</label>
                            </div>
                      	</div>
                        <span class="help-block" error-target="WorkPhone"></span>
                  	</div>
             	</div>
            </div>
			<input id="ContactCustomerID" type="hidden" class="form-control" name="CustomerId">
			<input type="hidden" class="form-control" name="resultMode" value="ajax">
            <div class="modal-footer">
                <button class="btn btn-light pull-left" type="button" data-dismiss="modal"><i class="fas fa-fw fa-times" aria-hidden="true" id="cancelContactSave"></i> Cancel</button>
                <button class="btn btn-success pull-right btn-flat" id="confirmContactSave"><i class="fas fa-fw fa-save" aria-hidden="true"></i>Save contact</button>
            </div>
        </div>
    </div>
</div>
<script>
window.addEventListener('load', function () {
	$('#cancelContactSave').click(function() {
		clearContactSelections();
		$('#_disabled_CustomerOption').attr('selected','selected');
		$('#ContactId').val('');
	});
	
	contactModalAction = '/contacts';
	
	$('#confirmContactSave').click(function() {
		xData = {};
		$('#createContactModal').find('.form-control').each(function () {
			if ($(this).val() != '')
				xData[$(this).attr('name')] = $(this).val();
		});
		
		$.ajax(
			contactModalAction,
			{
				type: 'POST',
				headers: {
        			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    			},
    			data: xData,
    			success: function (data, status, xhr) {
					if (data.status == 'success') {
                        clearContactSelections();
						
                        // Replace New Contact with Contact details as returned
						el = $('#_New_ContactOption');
						el.attr('value',data.contactId);
						el.prop('selected',true);
						el.text($('#FirstName').val() + ' ' + $('#LastName').val());
						$('#ContactId').val(data.contactId);
						
						// Change the modal to do updates
						contactModalAction = '/contacts/' + data.contactId + '/edit';
						
                        // Close the modal
                        $('#createContactModal').modal('hide');
					} else {
						// Clear then show errors
						$('.help-block').each(function() {
							$(this).html('');
						});
						
						$.each(data.errors, function( index, value ) {
							$('#createContactModal').find("[error-target='" + index + "']").html('<strong>' + value + '</strong>');
						});
					}
    			},
    			error: function (jqXhr, textStatus, errorMessage) {
            		alert('Contacts AJAX Error:' + errorMessage);
    			},
			}
		);
		
		return(false);
		
	});
	
	function clearContactSelections() {
		$('#ContactId').find('option:selected').each(function(){
                            $(this).removeAttr('selected');
                        });
	}
});
</script>