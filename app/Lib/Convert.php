<?php
namespace App\Lib;

class Convert
{
	public static function currency($value)
	{
		return ( $value ? str_replace(['£', ','], '', $value) : null);
	}
}
?>