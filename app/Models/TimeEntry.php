<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\AspNetUser;

class TimeEntry extends Authenticatable
{
    use Notifiable;

	protected $primaryKey = 'ID';

	public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'TimeDate', 'TimeUsed', 'TimeStatusID', 'AssignmentID', 'UserID', 'Comment'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    protected $casts = [
        'TimeDate' => 'date',
    ];

	protected $table = 'TimeEntries';

}
