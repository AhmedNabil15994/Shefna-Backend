<?php

Route::group(['prefix' => 'addon-categories', 'middleware' => 'CheckAddonsPermission'], function () {

    Route::get('/', 'Vendor\AddonCategoryController@index')
        ->name('vendor.addon_categories.index')
        ->middleware(['permission:show_addon_categories']);

    Route::get('datatable', 'Vendor\AddonCategoryController@datatable')
        ->name('vendor.addon_categories.datatable')
        ->middleware(['permission:show_addon_categories']);

    Route::get('create', 'Vendor\AddonCategoryController@create')
        ->name('vendor.addon_categories.create')
        ->middleware(['permission:add_addon_categories']);

    Route::post('/', 'Vendor\AddonCategoryController@store')
        ->name('vendor.addon_categories.store')
        ->middleware(['permission:add_addon_categories']);

    Route::get('{id}/edit', 'Vendor\AddonCategoryController@edit')
        ->name('vendor.addon_categories.edit')
        ->middleware(['permission:edit_addon_categories']);

    Route::put('{id}', 'Vendor\AddonCategoryController@update')
        ->name('vendor.addon_categories.update')
        ->middleware(['permission:edit_addon_categories']);

    Route::delete('{id}', 'Vendor\AddonCategoryController@destroy')
        ->name('vendor.addon_categories.destroy')
        ->middleware(['permission:delete_addon_categories']);

    Route::get('deletes', 'Vendor\AddonCategoryController@deletes')
        ->name('vendor.addon_categories.deletes')
        ->middleware(['permission:delete_addon_categories']);

    Route::get('{id}', 'Vendor\AddonCategoryController@show')
        ->name('vendor.addon_categories.show')
        ->middleware(['permission:show_addon_categories']);

});
