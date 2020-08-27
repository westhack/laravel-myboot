<?php

Route::prefix('backend/core/v1')->group(function() {

    Route::middleware('auth:api')->group(function() {

        Route::post('config/list', 'ConfigController@index');
        Route::post('config/create', 'ConfigController@create');
        Route::post('config/update', 'ConfigController@update');
        Route::post('config/delete', 'ConfigController@delete');
        Route::post('config/batch/update/value', 'ConfigController@batchUpdateValue');

        Route::post('dict/list', 'DictController@index');
        Route::post('dict/create', 'DictController@create');
        Route::post('dict/update', 'DictController@update');
        Route::post('dict/delete', 'DictController@delete');
        Route::post('dict/batch/update/value', 'DictController@batchUpdateValue');

    });

    Route::any('dict/all', 'DictController@all');
});

 Route::post('/core/activityLog/list', 'ActivityLogController@index');
 Route::post('/core/activityLog/create', 'ActivityLogController@create');
 Route::post('/core/activityLog/update', 'ActivityLogController@update');
 Route::post('/core/activityLog/delete', 'ActivityLogController@delete');
 Route::post('/core/activityLog/detail', 'ActivityLogController@detail');
 Route::post('/core/activityLog/all', 'ActivityLogController@all');
