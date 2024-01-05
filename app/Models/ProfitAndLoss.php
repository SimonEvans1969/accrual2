<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\AspNetUser;

class ProfitAndLoss extends Authenticatable
{
    use Notifiable;

    /***
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Month', 'Year', 'Section', 'Account', 'Project', 'Value',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    protected $casts = [
    ];

	protected $table = 'ProfitAndLoss';

}
