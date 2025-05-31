<?php

use App\Http\Controllers\ZohoWebHooksController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::resource('contacts', 'App\Http\Controllers\ContactsController')->only(['store', 'update']);

Route::post('/zoho/webhook/contact', [ZohoWebHooksController::class, 'newContact'])
    ->name('zoho.webhook.contact');
Route::put('/zoho/webhook/contact', [ZohoWebHooksController::class, 'editedContact'])
    ->name('zoho.webhook.contact.edited');
Route::post('/zoho/webhook/p_client', [ZohoWebHooksController::class, 'leadToContact'])
    ->name('zoho.webhook.p_client');
