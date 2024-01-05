<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class DealHistory extends Authenticatable
{
    use Notifiable;
	use SoftDeletes;

	const CREATED_AT = 'CreatedDate';
	const UPDATED_AT = 'LastUpdatedDate';

	protected $primaryKey = 'Id';

    protected $fillable = [
        'Id', 'Name', 'DealId', 'Description', 'DealStageId', 'PipelineId', 'DealTypeId', 'DealSourceId', 'Amount',
		'CloseDate', 'ClosedCancelledReason', 'ClosedLostReason', 'ClosedWonReason',
		'CurrentYearRevenueAllocation', 'MustWin', 'OwnerId', 'ContactId',
		'CreatedBy', 'CreatedDate', 'LastUpdatedBy', 'LastUpdatedDate'
    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    protected $dates = [ 'CloseDate', 'CreatedDate', 'LastUpdatedDate',
    ];

	protected $table = 'DealHistories';

}
