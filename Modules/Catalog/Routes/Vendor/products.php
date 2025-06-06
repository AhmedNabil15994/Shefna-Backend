<?php

Route::group(['prefix' => 'products'], function () {

    Route::get('products/switch/{id}/{action}', 'Vendor\ProductController@switcher')->name('vendor.products.switch');

    Route::get('/', 'Vendor\ProductController@index')
        ->name('vendor.products.index')
        ->middleware(['permission:show_products']);

    Route::get('datatable', 'Vendor\ProductController@datatable')
        ->name('vendor.products.datatable')
        ->middleware(['permission:show_products']);

    Route::get('create', 'Vendor\ProductController@create')
        ->name('vendor.products.create')
        ->middleware(['permission:add_products']);

    Route::post('/', 'Vendor\ProductController@store')
        ->name('vendor.products.store')
        ->middleware(['permission:add_products']);

    Route::get('{id}/edit', 'Vendor\ProductController@edit')
        ->name('vendor.products.edit')
        ->middleware(['permission:edit_products']);

    Route::put('{id}', 'Vendor\ProductController@update')
        ->name('vendor.products.update')
        ->middleware(['permission:edit_products']);

    Route::delete('{id}', 'Vendor\ProductController@destroy')
        ->name('vendor.products.destroy')
        ->middleware(['permission:delete_products']);

    Route::get('product/delete/image', 'Vendor\ProductController@deleteProductImage')
        ->name('vendor.products.delete_product_image')
        ->middleware(['permission:edit_products']);

    Route::get('deletes', 'Vendor\ProductController@deletes')
        ->name('vendor.products.deletes')
        ->middleware(['permission:delete_products']);

    Route::get('{id}', 'Vendor\ProductController@show')
        ->name('vendor.products.show')
        ->middleware(['permission:show_products']);

    Route::post('/update/photo', 'Vendor\ProductController@updatePhoto')
        ->name('vendor.products.update.photo')
        ->middleware(['permission:edit_products']);
});
