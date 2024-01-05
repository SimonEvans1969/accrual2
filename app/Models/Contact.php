<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Contact extends Authenticatable
{
    use Notifiable;

	const CREATED_AT = 'CreatedDate';
	const UPDATED_AT = 'LastUpdatedDate';

	protected $primaryKey = 'Id';

    protected $fillable = [
        'Id', 'ContactStatusId', 'ContactWarmth', 'PreferredContactMethodId',
		'TitleId', 'FirstName', 'LastName', 'Email', 'MobilePhone', 'WorkPhone',
		'CreatedBy', 'CreatedDate',
		'LastUpdatedBy', 'LastUpdatedDate',
		'OwnerId', 'Comments', 'Deleted'
    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    protected $dates = [
    ];

	protected $table = 'Contacts';

}
