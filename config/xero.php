<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Xero Defaults
    |--------------------------------------------------------------------------
    |
    | This option controls the default authentication "guard" and password
    | reset options for your application. You may change these defaults
    | as required, but they're a perfect start for most applications.
    |
    */

    'client_id' 			  	=> env('XERO_CLIENT_ID'),
	'client_secret'				=> env('XERO_CLIENT_SECRET'),
	'redirectUri'             	=> env('XERO_REDIRECT_URI'),
	
    'urlAuthorize'            	=> 'https://login.xero.com/identity/connect/authorize',
    'urlAccessToken'          	=> 'https://identity.xero.com/connect/token',
    'urlResourceOwnerDetails' 	=> 'https://api.xero.com/api.xro/2.0/Organisation',
	
	'scope'	=> [ 'openid',
		 		 'email',
		 		 'profile',
		 		 'offline_access',
		 		 'accounting.settings',
		 		 'accounting.transactions',
		 		 'accounting.contacts',
		 		 'accounting.journals.read',
		 		 'accounting.reports.read',
		 		 'accounting.attachments'
	   			],

];
?>