<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Customer extends Authenticatable
{
    use Notifiable;

	public $timestamps = false;

	protected $primaryKey = 'ID';

    protected $fillable = [
        'ID', 'Code', 'Name', 'AccountsPayableContact', 'Email',
		'active', 'Address1', 'Address2', 'Town', 'County', 'Postcode'
    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    protected $casts = [
    ];

	protected $table = 'Customers';

}
