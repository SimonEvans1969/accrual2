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
use App\Models\Contact;

class ContactsController extends Controller
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

		$contact = new Contact;

		$validator = $this->validateContact($request);

        if ($validator->fails()) {
        	return response()->json([
				'status' => 'failed',
				'errors' => $validator->errors(),
			]);
        }

		$contact->TitleId = $request->input('TitleId');
		$contact->FirstName = $request->input('FirstName');
		$contact->LastName = $request->input('LastName');
		$contact->Email = $request->input('Email');
		$contact->WorkPhone = $request->input('WorkPhone');

		$contact->OwnerId = $this->getCurrentAspNetUserId();
		$contact->CreatedBy = $this->getCurrentAspNetUserId();
		$contact->LastUpdatedBy = $this->getCurrentAspNetUserId();

		// Save the updates
		$contact->save();

		// Return to summary
		return response()->json([
				'status' => 'success',
				'message' => 'New contact saved',
				'contactId' => $contact->Id,
				'contactName' => $contact->FirstName . ' ' . $contact->LastName,
			]);
	}

	public function update( Request $request, $id )
	{

		$contact = Contact::find($id);
		if (!$contact) {
			 return back()->with(['Fatal' => 'Invalid Contact Id' ])
						  ->withInput()
						 ;
		}

		$validator = $this->validateContact($request);

        if ($validator->fails()) {
        	return response()->json([
				'status' => 'failed',
				'errors' => $validator->errors(),
			]);
        }

		$contact->TitleId = $request->input('TitleId');
		$contact->FirstName = $request->input('FirstName');
		$contact->LastName = $request->input('LastName');
		$contact->Email = $request->input('Email');
		$contact->WorkPhone = $request->input('WorkPhone');

		$contact->LastUpdatedBy = $this->getCurrentAspNetUserId();

		// Save the updates
		$contact->save();

		// Return to summary
		return response()->json([
				'status' => 'success',
				'message' => 'Contact updated',
				'contactId' => $contact->Id,
				'contactName' => $contact->FirstName . ' ' . $contact->LastName,
			]);
	}

	private function validateContact( Request $request)
	{
		$rules = [
			'TitleId'		=> 'nullable|exists:Titles,Id',
			'FirstName'		=> 'required|string',
			'LastName'		=> 'required|string',
			'Email'			=> 'email',
			'WorkPhone'		=> 'string',
			'CustomerID'	=> 'exists:Customers,ID',
			];

        $messages = [
			'TitleId.exists'			=> 'Invalid Title',
			'FirstName.required'		=> 'First Name cannot be blank',
			'FirstName.string'			=> 'Invalid First Name',
			'LastName.required'			=> 'Last Name cannot be blank',
			'LastName.string'			=> 'Invalid Last Name',
			'Email.email'				=> 'Invalid Email address',
			'WorkPhone.string'			=> 'Invalid Phone number',
			'CustomerID.exists'			=> 'Invalid Customer',

        ];

        $validator = Validator::make($request->all(), $rules, $messages);

		return ($validator);
	}
}
