<?php

// Knet
use App\Http\Controllers\Payment\KnetController;

// Define Knet callback route
Route::controller(KnetController::class)->group(function () {
    Route::get('/knet/callback', 'callback')->name('knet.callback');
});

// Admin routes with Knet functionality
Route::group(['prefix' =>'admin', 'middleware' => ['auth', 'admin']], function(){
    Route::resource('knet_method', 'KnetController');
    Route::get('/knet_configuration', 'KnetController@credentials_index')->name('knet.index');
    Route::get('/knet_transactions', 'KnetController@transactions_index')->name('knet.transactions');
    Route::post('/knet_configuration_update', 'KnetController@update_credentials')->name('knet.update_credentials');
});
