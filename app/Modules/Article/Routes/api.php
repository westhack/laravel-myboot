<?php


Route::prefix('article/v1')->group(static function () {
    Route::post('list', 'V1\ArticleController@index');
    Route::post('detail', 'V1\ArticleController@detail');
});

Route::prefix('backend/article/v1')->group(static function () {
    Route::post('article/list', 'Backend\ArticleController@index');
    Route::post('article/create', 'Backend\ArticleController@create');
    Route::post('article/update', 'Backend\ArticleController@update');
    Route::post('article/delete', 'Backend\ArticleController@delete');
    Route::get('article/detail', 'Backend\ArticleController@detail');
    Route::get('article/all', 'Backend\ArticleController@all');

    Route::post('category/list', 'Backend\CategoryController@index');
    Route::post('category/create', 'Backend\CategoryController@create');
    Route::post('category/update', 'Backend\CategoryController@update');
    Route::post('category/delete', 'Backend\CategoryController@delete');
    Route::get('category/detail', 'Backend\CategoryController@detail');
    Route::get('category/all', 'Backend\CategoryController@all');
});
