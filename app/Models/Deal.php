<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deal extends Authenticatable
{
    use Notifiable;
	use SoftDeletes;

	const CREATED_AT = 'CreatedDate';
	const UPDATED_AT = 'LastUpdatedDate';

	protected $primaryKey = 'Id';

    protected $fillable = [
        'Id', 'Name', 'Description', 'DealStageId', 'PipelineId',
		'Amount', 'CloseDate', 'Closed Cancelled Reason', 'ClosedLostReason', 'ClosedWonReason',
		'CurrentYearRevenueAllocation', 'MustWin', 'OwnerId',
		'CreatedBy', 'CreatedDate',
		'LastUpdatedBy', 'LastUpdatedDate',
		'DealTypeId', 'ContactId', 'DealSubTypes_id', 'DealSourceId',
    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    protected $dates = [ 'CloseDate', 'CreatedDate', 'LastUpdatedDate',
    ];

	protected $table = 'Deals';

}
