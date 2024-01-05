<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;


class Invoice extends Authenticatable
{
    use Notifiable;


	protected $primaryKey = 'InvoiceID';
    public $incrementing = false;
	protected $keyType = 'string';

	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
            'InvoiceID', 'Type', 'ContactName', 'InvoiceDate', 'Status', 'LineAmountTypes',
			'SubTotal', 'TotalTax', 'Total', 'TotalDiscount', 'CurrencyCode', 'CurrencyRate',
			'InvoiceNumber', 'Reference', 'AmountDue', 'AmountPaid', 'AmountCredited',
			'RemainingCredit', 'XeroUpdatedDateUTC'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    protected $casts = [
		'InvoiceDate' => 'date',
    ];

	protected $table = 'Invoices';

}
