<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class CostAccrual extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'Year', 'Month', 'Value', 'Comment',
		'AssignmentID', 'Status',
    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    protected $dates = [
    ];

	protected $table = 'CostAccruals';

}
