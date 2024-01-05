<div class="modal fade modal-danger" id="confirmDeleteModal" role="dialog" aria-labelledby="confirmDeleteLabel" aria-hidden="true" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Delete Deal
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    <span class="sr-only">close</span>
                </button>
            </div>
            <div class="modal-body">
                <p>
                    Are you sure you want to delete this deal? 
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light pull-left" type="button" data-dismiss="modal"><i class="fas fa-fw fa-times" aria-hidden="true"></i> Cancel</button>
				<form id="deleteConfirmForm" action="tbc" method="post" class="needs-validation">
    				{!! csrf_field() !!}
					{{ method_field('delete') }}
                	<button class="btn btn-danger pull-right btn-flat" type="submit" id="confirm"><i class="fas fa-fw fa-trash-alt" aria-hidden="true"></i> Delete this Deal</button>
				</form>
            </div>
        </div>
    </div>
</div>
