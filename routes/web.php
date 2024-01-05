<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::get('/', 'HomeController@index')->name('home');
Route::get('/home', 'HomeController@index')->name('home');
Route::resource('projects','ProjectsController');
Route::get('accrual/{id}','ProjectsController@accrual')->name('accrual');;
Route::resource('costaccruals','CostAccrualsController');
Route::resource('deals','DealsController');
Route::resource('contacts','ContactsController');
Route::resource('customers','CustomersController');
Route::get('/dealschart', 'DealsController@chart')->name('chart');

Route::get('/xero/authorize', 'XeroController@xero_auth')->name('xero.auth');
Route::get('/xero/callback', 'XeroController@xero_callback')->name('xero.callback');
Route::get('/xero/get', 'XeroController@xero_get')->name('xero.get');
Route::get('/xero/getpl', 'XeroController@xero_get_PL')->name('xero.getpl');
Route::get('/xero/test', 'XeroController@test')->name('xero.test');

Route::get('projectsprofit', 'ProfitabilityController@show')->name('projectsprofit');

//laravel-users
Route::group(['middleware' => 'auth'], function () {
    Route::resource('users', 'UsersManagementController', [
        'names' => [
            'index'   => 'users',
            'destroy' => 'user.destroy',
        ],
    ]);
});

Route::middleware(['web', 'auth'])->group(function () {
    Route::post('search-users', 'UsersManagementController@search')->name('search-users');
});

Route::get('routes', function() {
    $routeCollection = Route::getRoutes();

    echo "<table style='width:100%'>";
    echo "<tr>";
    echo "<td width='10%'><h4>HTTP Method</h4></td>";
    echo "<td width='10%'><h4>Route</h4></td>";
    echo "<td width='80%'><h4>Corresponding Action</h4></td>";
    echo "</tr>";
    foreach ($routeCollection as $value) {
        echo "<tr>";
        echo "<td>" . $value->methods()[0] . "</td>";
        echo "<td>" . $value->uri() . "</td>";
        echo "<td>" . $value->getActionName() . "</td>";
        echo "</tr>";
    }
    echo "</table>";
});
