<?php

Route::prefix('user/v1')->group(static function () {

    Route::prefix('auth')->group(static function () {
        Route::post('login', 'Frontend\V1\Auth\LoginController@login'); // 账号密码登录
        Route::post('logout', 'Frontend\V1\Auth\LoginController@logout'); //退出
        Route::post('refresh/token', 'Frontend\V1\Auth\LoginController@refresh'); // 刷新token

        Route::post('register', 'Frontend\V1\Auth\RegisterController@register'); // 注册
        Route::post('phone/register', 'Frontend\V1\Auth\PhoneRegisterController@register')->middleware('checkSms'); // 手机号注册

        Route::post('forgot/password', 'Frontend\V1\Auth\ForgotPasswordController@sendResetPhoneCode'); // 手机发送验证码
        Route::post('forgot/reset/password', 'Frontend\V1\Auth\ForgotResetPasswordController@phone'); // 手机重置密码
        Route::get('/user', 'Frontend\V1\Auth\UserController@user')->middleware('auth:api');
    });

    Route::prefix('account')->middleware('auth:api')->group(static function () {
        Route::post('profile', 'Frontend\V1\Account\ProfileController@profile'); // 用户资料修改
        Route::post('reset/password', 'Frontend\V1\Account\PasswordController@resetPassword'); // 修改密码
    });

});


Route::prefix('backend/user/v1')->group(static function () {
    Route::prefix('/account')->middleware('auth:api')->group(static function () {
        Route::post('profile', 'Backend\V1\Account\ProfileController@profile'); // 用户资料修改
        Route::post('reset/password', 'Backend\V1\Account\PasswordController@resetPassword'); // 修改密码
        Route::post('menu/create', 'Backend\V1\Account\MenuController@create');
        Route::post('menu/delete', 'Backend\V1\Account\MenuController@delete');
    });

    Route::get('/user/messages', 'Backend\V1\MessageController@index')->middleware('auth:api');
    Route::post('/user/message/delete', 'Backend\V1\MessageController@delete')->middleware('auth:api');
    Route::post('/user/message/view', 'Backend\V1\MessageController@view')->middleware('auth:api');

    Route::prefix('auth')->group(static function () {
        Route::get('/user', 'Backend\V1\Auth\UserController@user')->middleware('auth:api');
        Route::get('/menus', 'Backend\V1\Auth\UserController@menus')->middleware('auth:api');
        Route::post('/login', 'Backend\V1\Auth\LoginController@login')->middleware('userLog:用户登录'); // 账号密码登录
        Route::post('/logout', 'Backend\V1\Auth\LoginController@logout'); //退出
        Route::post('/refresh/token', 'Backend\V1\Auth\LoginController@refresh'); // 刷新token
        Route::post('/register', 'Backend\V1\Auth\RegisterController@register')->middleware('checkSms'); // 注册
        Route::post('/phone/register', 'Backend\V1\Auth\PhoneRegisterController@register'); // 手机号注册
        Route::post('/forgot/password', 'Backend\V1\Auth\ForgotPasswordController@sendResetPhoneCode'); // 手机发送验证码
        Route::post('/forgot/reset/password', 'Backend\V1\Auth\ForgotResetPasswordController@phone'); // 手机重置密码
    });

    Route::middleware(['auth:api', 'routePermission'])->group(static function () {

        Route::post('/user/list', 'Backend\V1\UserController@index'); // 用户列表
        Route::post('/user/create', 'Backend\V1\UserController@create'); // 用户创建
        Route::post('/user/update', 'Backend\V1\UserController@update'); // 用户更新
        Route::post('/user/delete', 'Backend\V1\UserController@delete'); // 用户删除
        Route::post('/user/batch/update', 'Backend\V1\UserController@batchUpdate'); // 用户批量更新

        Route::post('/admin/list', 'Backend\V1\AdminController@index'); // 后端用户列表
        Route::post('/admin/create', 'Backend\V1\AdminController@create'); // 后端用户创建
        Route::post('/admin/update', 'Backend\V1\AdminController@update'); // 后端用户更新
        Route::post('/admin/delete', 'Backend\V1\AdminController@delete'); // 后端用户删除
        Route::post('/admin/batch/update', 'Backend\V1\AdminController@batchUpdate'); // 后端用户批量更新

        Route::post('/permission/list', 'Backend\V1\Permission\PermissionController@index');
        Route::post('/permission/create', 'Backend\V1\Permission\PermissionController@create');
        Route::post('/permission/update', 'Backend\V1\Permission\PermissionController@update');
        Route::post('/permission/delete', 'Backend\V1\Permission\PermissionController@delete');
        Route::post('/permission/routes', 'Backend\V1\Permission\PermissionController@routes');
        Route::post('/permission/all', 'Backend\V1\Permission\PermissionController@all');

        Route::any('/permission/export', 'Backend\V1\Permission\PermissionController@export');
        Route::any('/permission/import', 'Backend\V1\Permission\PermissionController@import');

        Route::post('/role/list', 'Backend\V1\Permission\RoleController@index'); //->middleware('routePermission');
        Route::post('/role/create', 'Backend\V1\Permission\RoleController@create');
        Route::post('/role/update', 'Backend\V1\Permission\RoleController@update');
        Route::post('/role/delete', 'Backend\V1\Permission\RoleController@delete');
        Route::post('/role/all', 'Backend\V1\Permission\RoleController@all');

        Route::post('/role/give/permission', 'Backend\V1\Permission\RoleController@givePermission');
        Route::post('/role/sync/permissions', 'Backend\V1\Permission\RoleController@syncPermissions');
        Route::post('/role/revoke/permission', 'Backend\V1\Permission\RoleController@revokePermission');

        Route::post('/user/give/permission', 'Backend\V1\Permission\UserController@givePermission');
        Route::post('/user/sync/permissions', 'Backend\V1\Permission\UserController@syncPermissions');
        Route::post('/user/revoke/permission', 'Backend\V1\Permission\UserController@revokePermission');

        Route::post('/user/assign/role', 'Backend\V1\Permission\UserController@assignRole');
        Route::post('/user/remove/role', 'Backend\V1\Permission\UserController@removeRole');
        Route::post('/user/sync/roles', 'Backend\V1\Permission\UserController@syncRoles');

        Route::post('/guard/all', 'Backend\V1\Permission\GuardController@all'); // 后端守卫列表

    });
});
