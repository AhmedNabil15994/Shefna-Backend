<?php

Route::group(['prefix' => 'products'], function () {

    Route::get('{id}/add-ons', 'Vendor\ProductAddonsController@addOns')
        ->name('vendor.products.add_ons')
        ->middleware(['permission:show_product_addons']);

    Route::post('{id}/store-add-ons', 'Vendor\ProductAddonsController@storeAddOns')
        ->name('vendor.products.store_add_ons')
        ->middleware(['permission:show_product_addons']);

    Route::get('add-ons/delete', 'Vendor\ProductAddonsController@deleteAddOns')
        ->name('vendor.products.delete_add_ons')
        ->middleware(['permission:show_product_addons']);

    Route::get('add-ons/delete/option', 'Vendor\ProductAddonsController@deleteAddOnsOption')
        ->name('vendor.products.delete_add_ons_option')
        ->middleware(['permission:show_product_addons']);
});
