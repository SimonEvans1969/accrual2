<div class="modal fade modal-primary" id="costaccrualModal" role="dialog" aria-labelledby="costaccrualModal" aria-hidden="true" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Update Cost Accrual
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    <span class="sr-only">close</span>
                </button>
            </div>
            <div class="modal-body">
				<div class="form-group has-feedback row">
                	<label class="col-md-3 control-label">Name</label>
					<div class="col-md-9">
                    	<div class="input-group">
                        	<input id="Name" class="form-control" type="text">
                            <div class="input-group-append">
                            	<label class="input-group-text" for="Name">
                                	<i class="fas fa-fw fa-id-badge" aria-hidden="true"></i>
   			 					</label>
                            </div>
                      	</div>
                  	</div>
             	</div>
				<div class="form-group has-feedback row">
                	<label class="col-md-3 control-label">Company</label>
					<div class="col-md-9">
                    	<div class="input-group">
                        	<input id="Company" class="form-control" type="text">
                            <div class="input-group-append">
                            	<label class="input-group-text" for="Company">
                                	<i class="fas fa-fw fa-building" aria-hidden="true"></i>
   			 					</label>
                            </div>
                      	</div>
                  	</div>
             	</div>
				<div class="form-group has-feedback row">
                	<label class="col-md-3 control-label">Project</label>
					<div class="col-md-9">
                    	<div class="input-group">
                        	<input id="Project" class="form-control" type="text">
                            <div class="input-group-append">
                            	<label class="input-group-text" for="Project">
                                	<i class="fas fa-fw fa-tasks" aria-hidden="true"></i>
   			 					</label>
                            </div>
                      	</div>
                  	</div>
             	</div>
				<div class="form-group has-feedback row">
                	<label class="col-md-3 control-label">Date</label>
					<div class="col-md-9">
                    	<div class="input-group">
                        	<input id="Month" name="Month" class="form-control" type="text">
							&nbsp;/&nbsp;
							<input id="Year" name="Year" class="form-control" type="text">
                            <div class="input-group-append">
                            	<label class="input-group-text" for="Date">
                                	<i class="fas fa-fw fa-calendar-week" aria-hidden="true"></i>
   			 					</label>
                            </div>
                      	</div>
                  	</div>
             	</div>
				<div class="form-group has-feedback row">
                	<label class="col-md-3 control-label">Proposed Accrual</label>
					<div class="col-md-9">
                    	<div class="input-group">
                        	<input id="Cost" class="form-control" type="text" data-type="currency">
                            <div class="input-group-append">
                            	<label class="input-group-text" for="Cost">
                                	<i class="fas fa-fw fa-pound-sign" aria-hidden="true"></i>
   			 					</label>
                            </div>
                      	</div>
                  	</div>
             	</div>
				<div class="form-group has-feedback row">
                	<label class="col-md-3 control-label">Correction</label>
					<div class="col-md-9">
                    	<div class="input-group">
                        	<input id="Correction" name="Correction" class="form-control" data-type="currency" type="text">
                            <div class="input-group-append">
                            	<label class="input-group-text" for="Correction">
                                	<i class="fas fa-fw fa-pound-sign" aria-hidden="true"></i>
   			 					</label>
                            </div>
                      	</div>
						<span class="help-block" error-target="Correction"></span>
                  	</div>
             	</div>
				<div class="form-group has-feedback row">
                	<label class="col-md-3 control-label">Comment</label>
					<div class="col-md-9">
                    	<div class="input-group">
                        	<textarea class="form-control" id="CorrectionComment" name="CorrectionComment" 
									  rows="3"></textarea>
                        <div class="input-group-append">
                            <label class="input-group-text" for="CorrectionComment">
                                <i class="fas fa-fw fa-question-circle" aria-hidden="true"></i>
                            </label>
                        </div>
                      	</div>
						<span class="help-block" error-target="CorrectionComment"></span>
                  	</div>
             	</div>
				<div class="form-group has-feedback row">
                	<label class="col-md-3 control-label">Accrual</label>
					<div class="col-md-9">
                    	<div class="input-group">
                        	<input id="Accrual" name="Accrual" class="form-control" data-type="currency" type="text">
                            <div class="input-group-append">
                            	<label class="input-group-text" for="Accrual">
                                	<i class="fas fa-fw fa-pound-sign" aria-hidden="true"></i>
   			 					</label>
                            </div>
                      	</div>
						<span class="help-block" error-target="Accrual"></span>
                  	</div>
             	</div>
				<div class="form-group has-feedback row">
                	<label class="col-md-3 control-label">Comment</label>
					<div class="col-md-9">
                    	<div class="input-group">
                        	<textarea class="form-control" id="AccrualComment" name="AccrualComment" 
									  rows="3"></textarea>
                        <div class="input-group-append">
                            <label class="input-group-text" for="AccrualComment">
                                <i class="fas fa-fw fa-question-circle" aria-hidden="true"></i>
                            </label>
                        </div>
                      	</div>
						<span class="help-block" error-target="AccrualComment"></span>
                  	</div>
             	</div>
            </div>
			<input id="AssignmentID" type="hidden" class="form-control" name="AssignmentID">
            <div class="modal-footer">
                <button class="btn btn-light pull-left" type="button" data-dismiss="modal"><i class="fas fa-fw fa-times" aria-hidden="true" id="cancelSave"></i> Cancel</button>
                <button class="btn btn-success pull-right btn-flat" id="confirmSave"><i class="fas fa-fw fa-save" aria-hidden="true"></i>Save Accrual</button>
            </div>
        </div>
    </div>
</div>
<script>
window.addEventListener('load', function () {
	
	$('#costaccrualModal').find('#confirmSave').click(function() {
		xData = {};
		$('#costaccrualModal').find('.form-control').each(function () {
			if (($(this).val() != '') && ($(this).attr('name')))
				xData[$(this).attr('name')] = $(this).val();
		});
		
		$.ajax(
			'/costaccruals',
			{
				type: 'POST',
				headers: {
        			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    			},
    			data: xData,
    			success: function (data, status, xhr) {
					if (data.status == 'success') {
						if (data.warnings) {
							$.each(data.warnings, function (index, value) {
								alert('Warning:' + value);
							});
						}
                        // Close the modal
                        $('#costaccrualModal').modal('hide');
						// Reload the page
						$('#reload').submit();
					} else {
						// Clear then show errors
						$('.help-block').each(function() {
							$(this).html('');
						});
						
						$.each(data.errors, function( index, value ) {
							$('#costaccrualModal').find("[error-target='" + index + "']").html('<strong>' + value + '</strong>');
						});
					}
    			},
    			error: function (jqXhr, textStatus, errorMessage) {
            		alert('costaccruals AJAX Error:' + errorMessage);
    			},
			}
		);
		
		return(false);
		
	});
});
</script>