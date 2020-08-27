<?php

Route::middleware(['auth:api', 'routePermission'])->group(static function () {
    Route::get('/backend/generator/form', 'GeneratorController@form');
    Route::post('/backend/generator/getRelations', 'GeneratorController@getRelations');
    Route::post('/backend/generator/getTableColumns', 'GeneratorController@getTableColumns');
    Route::post('/backend/generator/model', 'GeneratorController@model');
    Route::post('/backend/generator/controller', 'GeneratorController@controller');
});
