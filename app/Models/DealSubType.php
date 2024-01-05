<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class DealSubType extends Authenticatable
{
    use Notifiable;

	const CREATED_AT = 'CreatedDate';
	const UPDATED_AT = 'LastUpdatedDate';

	protected $primaryKey = 'Id';

    protected $fillable = [
        'DealTypeId', 'Description',
		'CreatedBy',
		'LastUpdatedBy',
    ];

    protected $hidden = [];

    protected $casts = [
    ];

	protected $table = 'DealSubTypes';

}
