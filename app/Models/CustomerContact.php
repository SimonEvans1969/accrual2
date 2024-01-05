<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class CustomerContact extends Authenticatable
{
    use Notifiable;

	const CREATED_AT = 'CreatedDate';
	const UPDATED_AT = 'LastUpdatedDate';

	protected $primaryKey = 'Id';

    protected $fillable = [
        'ContactId', 'CustomerID', 'StartDate', 'EndDate',
		'CreatedBy', 'CreatedDate',
		'LastUpdatedBy', 'LastUpdatedDate'
    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    protected $casts = [
    ];

	protected $table = 'CustomerContacts';

}
