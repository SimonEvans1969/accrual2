<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class DealStage extends Authenticatable
{
    use Notifiable;

	const CREATED_AT = 'CreatedDate';
	const UPDATED_AT = 'UpdatedDate';

	protected $primaryKey = 'Id';

    protected $fillable = [
        'Id', 'Description',
		'CreatedBy', 'CreatedDate',
		'LastUpdatedBy', 'LastUpdatedDate',
    ];

    protected $hidden = [];

    protected $casts = [
    ];

	protected $table = 'DealStages';

}
