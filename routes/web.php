<?php

Route::group(['middleware' => ['auth']], function () {
    Route::get('admin/package-zj-adoa', 'PackageZjAdoaController@index')->name('package.adoa.index');
    Route::get('package-zj-adoa', 'PackageZjAdoaController@index')->name('package.adoa.tab.index');
});
