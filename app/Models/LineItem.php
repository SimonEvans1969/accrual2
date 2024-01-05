<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;


class LineItem extends Authenticatable
{
    use Notifiable;


	protected $primaryKey = 'LineItemID';
    public $incrementing = false;
	protected $keyType = 'string';

	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
                'LineItemID', 'InvoiceID', 'Description', 'Quantity', 'UnitAmount', 'ItemCode',
				'AccountCode', 'TaxType', 'TaxAmount', 'LineAmount', 'DiscountRate', 'DiscountAmount',
				'Naut_Account', 'Naut_Project'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    protected $dates = [
    ];

	protected $table = 'LineItems';

}
