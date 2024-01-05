<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class CostAccrualCorrection extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'Year', 'Month', 'Cost', 'Comment',
		'AssignmentID',
    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    protected $dates = [
    ];

	protected $table = 'CostAccrualCorrections';

}
