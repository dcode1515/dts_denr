<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
/*AUTH*/
Route::get('/', [App\Http\Controllers\Auth\AuthController::class, 'login']);
Route::post('/api/login', [App\Http\Controllers\Auth\AuthController::class, 'post_login']);
Route::post('/logout', [App\Http\Controllers\Auth\AuthController::class, 'logout'])->name('logout');

/*END AUTH*/

/*SUPER ADMIN*/
Route::get('/dashboard-super-admin', [App\Http\Controllers\SuperAdminController::class, 'index'])->name('dashboard');
Route::get('/main-office', [App\Http\Controllers\SuperAdminController::class, 'main_office'])->name('main.office');
Route::post('/api/main-offices', [App\Http\Controllers\SuperAdminController::class, 'store_main_office']);
Route::put('/api/main-offices/{id}', [App\Http\Controllers\SuperAdminController::class, 'update_main_office']);
Route::get('/api/main-offices', [App\Http\Controllers\SuperAdminController::class, 'get_main_offices']);
Route::delete('/api/main-offices/{id}', [App\Http\Controllers\SuperAdminController::class, 'delete_main_office']);
Route::get('/sub-office', [App\Http\Controllers\SuperAdminController::class, 'sub_office'])->name('sub.office');
Route::get('/api/main-offices/data', [App\Http\Controllers\SuperAdminController::class, 'get_data_main_office']);

Route::post('/api/sub-offices', [App\Http\Controllers\SuperAdminController::class, 'store_sub_office']);
Route::put('/api/sub-offices/{id}', [App\Http\Controllers\SuperAdminController::class, 'update_sub_office']);
Route::get('/api/sub-offices', [App\Http\Controllers\SuperAdminController::class, 'get_sub_offices']);
Route::delete('/api/sub-offices/{id}', [App\Http\Controllers\SuperAdminController::class, 'delete_sub_office']);
/*end SUPER ADMIN*/


/*ADMIN RECORDS*/
Route::get('/dashboard-admin', [App\Http\Controllers\Admin\AdminController::class, 'dashboard'])->name('admin.dashboard');
Route::get('/document-type', [App\Http\Controllers\Admin\AdminController::class, 'document_type'])->name('admin.document.type');
Route::get('/api/document-type', [App\Http\Controllers\Admin\AdminController::class, 'get_document_types']);
Route::post('/api/document-type', [App\Http\Controllers\Admin\AdminController::class, 'store_document_type']);
Route::put('/api/document-type/{id}', [App\Http\Controllers\Admin\AdminController::class, 'update_document_type']);
Route::delete('/api/document-type/{id}', [App\Http\Controllers\Admin\AdminController::class, 'delete_document_type']);
Route::get('/incoming-documents', [App\Http\Controllers\Admin\AdminController::class, 'incoming_documents'])->name('admin.incoming.documents');
Route::post('/api/reserve-tracking', [App\Http\Controllers\Admin\AdminController::class, 'storeReserveTracking']);
Route::get('/api/reserve-tracking', [App\Http\Controllers\Admin\AdminController::class, 'get_data_reserve_tracking']);
Route::get('/create-incoming/{id}', [App\Http\Controllers\Admin\AdminController::class, 'create_incoming']);
Route::get('/api/get/document-type', [App\Http\Controllers\Admin\AdminController::class, 'documentType']);
Route::get('/api/route-office', [App\Http\Controllers\Admin\AdminController::class, 'route_office']);
Route::put('/api/create-update-document/{id}', [App\Http\Controllers\Admin\AdminController::class, 'create_update_document']);
Route::put('/api/update-document/{id}', [App\Http\Controllers\Admin\AdminController::class, 'update_document']);

Route::get('/api/in-progress', [App\Http\Controllers\Admin\AdminController::class, 'get_data_in_progress']);
Route::get('/view-document/{token}', [App\Http\Controllers\Admin\AdminController::class, 'view_document']);
Route::post('/api/add-forward-document', [App\Http\Controllers\Admin\AdminController::class, 'forward_document']);

/*END ADMIN RECORDS*/