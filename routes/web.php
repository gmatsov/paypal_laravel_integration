<?php

Route::get('/', 'HomeController@index')->name('/');

Route::post('/payment/add-funds/paypal', 'PaymentController@payWithpaypal')->name('payWithpaypal');

Route::get('status', 'PaymentController@getPaymentStatus')->name('status');
