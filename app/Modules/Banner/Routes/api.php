<?php

Route::group(['prefix' => 'backend/banner/v1/'], function () {

    Route::post('banner/list', 'V1\BannerController@index');
    Route::post('banner/create', 'V1\BannerController@create');
    Route::post('banner/update', 'V1\BannerController@update');
    Route::post('banner/delete', 'V1\BannerController@delete');
    Route::post('banner/detail', 'V1\BannerController@detail');
    Route::post('banner/all', 'V1\BannerController@all');

    Route::post('category/list', 'V1\CategoryController@index');
    Route::post('category/create', 'V1\CategoryController@create');
    Route::post('category/update', 'V1\CategoryController@update');
    Route::post('category/delete', 'V1\CategoryController@delete');
    Route::post('category/detail', 'V1\CategoryController@detail');
    Route::post('category/all', 'V1\CategoryController@all');

});
