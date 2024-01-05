<div class="modal fade modal-primary" id="createCustomerModal" role="dialog" aria-labelledby="createCustomerLabel" aria-hidden="true" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Create Customer
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    <span class="sr-only">close</span>
                </button>
            </div>
            <div class="modal-body">
				<div class="form-group has-feedback row">
                	<label for="Code" class="col-md-3 control-label">Customer Code</label>
                   	<div class="col-md-9">
                    	<div class="input-group">
                        	<input id="Code" class="form-control text-uppercase" placeholder="Customer Code" name="Code" type="text">
                            <div class="input-group-append">
                            	<label class="input-group-text" for="Code">
                                	<i class="far fa-id-badge " aria-hidden="true"></i>
   			 					</label>
                            </div>
                      	</div>
                        <span class="help-block" error-target="Code"></span>
                  	</div>
             	</div>
				<div class="form-group has-feedback row">
                	<label for="Name" class="col-md-3 control-label">Customer Name</label>
                   	<div class="col-md-9">
                    	<div class="input-group">
                        	<input id="Name" class="form-control" placeholder="Customer Name" name="Name" type="text">
                            <div class="input-group-append">
                            	<label class="input-group-text" for="Name">
                                	<i class="far fa-id-badge " aria-hidden="true"></i>
   			 					</label>
                            </div>
                      	</div>
                        <span class="help-block" error-target="Name"></span>
                  	</div>
             	</div>
            </div>
			<input type="hidden" class="form-control" name="CustomerResultMode" value="ajax">
            <div class="modal-footer">
                <button class="btn btn-light pull-left" type="button" data-dismiss="modal"><i class="fas fa-fw fa-times" aria-hidden="true" id="cancelCustomerSave"></i> Cancel</button>
                <button class="btn btn-success pull-right btn-flat" id="confirmCustomerSave"><i class="fas fa-fw fa-save" aria-hidden="true"></i>Save customer</button>
            </div>
        </div>
    </div>
</div>
<script>
window.addEventListener('load', function () {
	$('#cancelCustomerSave').click(function() {
		clearCustomerSelections();
		$('#_disabled_CustomerOption').attr('selected','selected');
		$('#CustomerId').val('');
	});
	
	customerModalAction = '/customers';
	
	$('#confirmCustomerSave').click(function() {
		$('#createCustomerModal').find('.text-uppercase').each(function () {
			$(this).val($(this).val().toUpperCase());
		});
		
		xData = {};
		$('#createCustomerModal').find('.form-control').each(function () {
			if ($(this).val() != '')
				xData[$(this).attr('name')] = $(this).val();
		});
		
		$.ajax(
			customerModalAction,
			{
				type: 'POST',
				headers: {
        			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    			},
    			data: xData,
    			success: function (data, status, xhr) {
					if (data.status == 'success') {
                        clearCustomerSelections();
						
                        // Replace New Customer with Customer details as returned
						el = $('#_New_CustomerOption');
						el.attr('value',data.customerId);
						el.prop('selected',true);
						el.text(data.customerName);
						$('#CustomerId').val(data.customerId);
						
						// Change the modal to do updates
						customerModalAction = '/customers/' + data.customerId + '/edit';
						
                        // Close the modal
                        $('#createCustomerModal').modal('hide');
					} else {
						// Clear then show errors
						$('.help-block').each(function() {
							$(this).html('');
						});
						
						$.each(data.errors, function( index, value ) {
							$('#createCustomerModal').find("[error-target='" + index + "']").html('<strong>' + value + '</strong>');
						});
					}
    			},
    			error: function (jqXhr, textStatus, errorMessage) {
            		alert('Customers AJAX Error:' + errorMessage);
    			},
			}
		);
		
		return(false);
		
	});
	
	function clearCustomerSelections() {
		$('#CustomerId').find('option:selected').each(function(){
                            $(this).removeAttr('selected');
                        });
	}
});
</script>