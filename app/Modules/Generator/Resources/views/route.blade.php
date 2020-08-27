
Route::post('{{$apiList['list']}}', '{{$controllerClassName}}@index');
Route::post('{{$apiList['create']}}', '{{$controllerClassName}}@create');
Route::post('{{$apiList['update']}}', '{{$controllerClassName}}@update');
Route::post('{{$apiList['delete']}}', '{{$controllerClassName}}@delete');
Route::post('{{$apiList['detail']}}', '{{$controllerClassName}}@detail');
Route::post('{{$apiList['all']}}', '{{$controllerClassName}}@all');
