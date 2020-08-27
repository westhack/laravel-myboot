<?php

Route::group(['prefix' => 'storage/v1',], function () {

    Route::post('upload/file', 'StorageController@uploadFile'); // 上传文件
    Route::post('upload/image', 'StorageController@uploadImage'); // 上传图片

});
