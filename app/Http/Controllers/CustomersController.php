<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Carbon\Carbon;
use DateTime;
use Validator;
use App\Models\AspNetUser;
use App\Models\Customer;
use App\Ruless\Uppercase;

class CustomersController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
	}


	public function store( Request $request )
	{

		$customer = new Customer;

		$validator = $this->validateCustomer($request);

        if ($validator->fails()) {
        	return response()->json([
				'status' => 'failed',
				'errors' => $validator->errors(),
			]);
        }

		$customer->Code = $request->input('Code');
		$customer->Name = $request->input('Name');
		$customer->active = 1;

		// Save the updates
		$customer->save();

		// Return to summary
		return response()->json([
				'status' => 'success',
				'message' => 'New customer saved',
				'customerId' => $customer->ID,
				'customerName' => $customer->Name,
			]);
	}

	public function update( Request $request, $id )
	{

		$customer = Contact::find($id);
		if (!$customer) {
			 return back()->with(['Fatal' => 'Invalid Customer Id' ])
						  ->withInput()
						 ;
		}

		$validator = $this->validateCustomer($request);

        if ($validator->fails()) {
        	return response()->json([
				'status' => 'failed',
				'errors' => $validator->errors(),
			]);
        }

		$customer->Code = $request->input('Code');
		$customer->Name = $request->input('Name');
		$customer->active = 1;

		// Save the updates
		$contact->save();

		// Return to summary
		return response()->json([
				'status' => 'success',
				'message' => 'Customer updated',
				'customerId' => $customer->ID,
				'customerName' => $customer->Name,
			]);
	}

	private function validateCustomer( Request $request)
	{
		$rules = [
			'Code'			=> [ 'required','alpha', new Uppercase, 'min:3','max:4', 'unique:Customers,Code' ],
			'Name'			=> 'required|string|min:10',
			];

        $messages = [
			'Code.required'				=> 'Customer Code cannot be blank',
			'Code.alpha'				=> 'Customer Code can only include A-Z characters',
			'Code.min'					=> 'Customer Code must be at least 3 characters',
			'Code.max'					=> 'Customer Code cannot be more than 4 characters',
			'Code.unique'				=> 'Customer Code must be unique',
			'Name.required'				=> 'Customer Name cannot be blank',
			'Name.string'				=> 'Invalid Customer Name',
			'Name.min'					=> 'Customer Name must be at least 10 characters',

        ];

        $validator = Validator::make($request->all(), $rules, $messages);

		return ($validator);
	}
}
