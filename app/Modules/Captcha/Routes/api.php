<?php

Route::prefix('captcha/v1')->group(function() {

    Route::get('img/api', 'ImgController@api');
    Route::post('sms/send', 'SmsController@send');

});

Route::prefix('backend/captcha/v1')->group(function() {
    Route::post('sms/list', 'SmsController@index')->middleware('auth:api');
    Route::post('sms/delete', 'SmsController@delete')->middleware('auth:api');
});
