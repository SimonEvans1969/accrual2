<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\AspNetUser;

class Project extends Authenticatable
{
    use Notifiable;

    /***
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ID', 'Code', 'Name', 'StartDate', 'EndDate', 'ClosureDate', 'InternalProject',
		'ProjectTypeID', 'DealTypeId', 'CustomerId',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    protected $casts = [
		'StartDate' => 'date',
        'EndDate' => 'date',
        'ClosureDate' => 'date',
    ];

	protected $table = 'Projects';

}
