<?php

use Modules\Catalog\Http\Middleware\CheckAddonsPermissionMiddleware;

Route::group(['prefix' => 'addon-options'], function () {

    Route::middleware([CheckAddonsPermissionMiddleware::class])->group(function () {

        Route::get('/', 'Vendor\AddonOptionsController@index')
            ->name('vendor.addon_options.index')
            ->middleware(['permission:show_addon_options']);

        Route::get('datatable', 'Vendor\AddonOptionsController@datatable')
            ->name('vendor.addon_options.datatable')
            ->middleware(['permission:show_addon_options']);

        Route::get('create', 'Vendor\AddonOptionsController@create')
            ->name('vendor.addon_options.create')
            ->middleware(['permission:add_addon_options']);

        Route::post('/', 'Vendor\AddonOptionsController@store')
            ->name('vendor.addon_options.store')
            ->middleware(['permission:add_addon_options']);

        Route::get('{id}/edit', 'Vendor\AddonOptionsController@edit')
            ->name('vendor.addon_options.edit')
            ->middleware(['permission:edit_addon_options']);

        Route::put('{id}', 'Vendor\AddonOptionsController@update')
            ->name('vendor.addon_options.update')
            ->middleware(['permission:edit_addon_options']);

        Route::delete('{id}', 'Vendor\AddonOptionsController@destroy')
            ->name('vendor.addon_options.destroy')
            ->middleware(['permission:delete_addon_options']);

        Route::get('deletes', 'Vendor\AddonOptionsController@deletes')
            ->name('vendor.addon_options.deletes')
            ->middleware(['permission:delete_addon_options']);

        Route::get('{id}', 'Vendor\AddonOptionsController@show')
            ->name('vendor.addon_options.show')
            ->middleware(['permission:show_addon_options']);
    });

    Route::get('addon-category/get-all', 'Vendor\AddonOptionsController@getAddonOptionsByCategory')
        ->name('vendor.addon_options.get_by_addon_category');

    Route::get('get-addon-details/by-product-addon-id', 'Vendor\AddonOptionsController@getAddonDetails')
        ->name('vendor.addon_options.get_addon_details');

    Route::get('get-addon-categories/by-vendor', 'Vendor\AddonOptionsController@getAddonCategoriesByVendor')
        ->name('vendor.addon_options.get_addon_categories_by_vendor');
});
