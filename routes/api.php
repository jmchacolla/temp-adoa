<?php
Route::group(['middleware' => ['auth:api', 'bindings']], function() {
    Route::get('admin/package-zj-adoa/fetch', 'PackageZjAdoaController@fetch')->name('package.adoa.fetch');
    Route::apiResource('admin/package-zj-adoa', 'PackageZjAdoaController');
});
