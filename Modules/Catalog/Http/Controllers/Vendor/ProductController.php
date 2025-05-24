<?php

namespace Modules\Catalog\Http\Controllers\Vendor;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Catalog\Http\Requests\Vendor\ProductRequest;
use Modules\Catalog\Http\Requests\Vendor\updatePhotoRequest;
use Modules\Catalog\Repositories\Vendor\ProductRepository as Product;
use Modules\Catalog\Transformers\Vendor\ProductResource;
use Modules\Core\Traits\DataTable;

class ProductController extends Controller
{
    protected $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    public function index()
    {
        return view('catalog::vendor.products.index');
    }

    public function datatable(Request $request)
    {
        $datatable = DataTable::drawTable($request, $this->product->QueryTable($request));

        $datatable['data'] = ProductResource::collection($datatable['data']);

        return Response()->json($datatable);
    }

    public function create()
    {
        return view('catalog::vendor.products.create');
    }

    public function store(ProductRequest $request)
    {
        try {
            $create = $this->product->create($request);

            if ($create) {
                return Response()->json([true, __('apps::vendor.general.message_create_success')]);
            }

            return Response()->json([false, __('apps::vendor.general.message_error')]);
        } catch (\PDOException $e) {
            return Response()->json([false, $e->errorInfo[2]]);
        }
    }

    public function show($id)
    {
        return view('catalog::vendor.products.show');
    }

    public function edit($id)
    {
        $product = $this->product->findById($id);
        $product->load(["variantValues", "variants.productValues.optionValue.option", "categories"]);
        $currentVaration = $product->variantValues->sortBy("option_value_id")->groupBy("product_variant_id")->pluck("*.option_value_id")->toArray();

        return view('catalog::vendor.products.edit', compact('product', "currentVaration"));
    }

    public function update(ProductRequest $request, $id)
    {
        try {
            $update = $this->product->update($request, $id);

            if ($update) {
                return Response()->json([true, __('apps::vendor.general.message_update_success')]);
            }

            return Response()->json([false, __('apps::vendor.general.message_error')]);
        } catch (\PDOException $e) {
            return Response()->json([false, $e->errorInfo[2]]);
        }
    }

    public function destroy($id)
    {
        try {
            $delete = $this->product->delete($id);

            if ($delete) {
                return Response()->json([true, __('apps::vendor.general.message_delete_success')]);
            }

            return Response()->json([false, __('apps::vendor.general.message_error')]);
        } catch (\PDOException $e) {
            return Response()->json([false, $e->errorInfo[2]]);
        }
    }

    public function deletes(Request $request)
    {
        try {
            if (empty($request['ids'])) {
                return Response()->json([false, __('apps::dashboard.general.select_at_least_one_item')]);
            }

            $deleteSelected = $this->product->deleteSelected($request);
            if ($deleteSelected) {
                return Response()->json([true, __('apps::vendor.general.message_delete_success')]);
            }

            return Response()->json([false, __('apps::vendor.general.message_error')]);
        } catch (\PDOException $e) {
            return Response()->json([false, $e->errorInfo[2]]);
        }
    }

    public function deleteProductImage(Request $request)
    {
        try {
            $id = $request->id;
            $prdImg = $this->product->findProductImgById($id);

            if ($prdImg) {
                $delete = $this->product->deleteProductImg($id);
                if ($delete) {
                    return Response()->json([true, __('apps::dashboard.general.message_delete_success')]);
                } else {
                    return Response()->json([false, __('apps::dashboard.general.message_error')]);
                }

            }

            return Response()->json([false, __('apps::dashboard.general.message_error')]);
        } catch (\Exception $e) {
            return Response()->json([false, $e->errorInfo[2]]);
        }
    }

    public function updatePhoto(updatePhotoRequest $request)
    {
        try {
            $imagePath = $this->product->updatePhoto($request);

            if ($imagePath) {
                return Response()->json([true, __('apps::dashboard.general.message_create_success'), 'imagePath' => $imagePath]);
            }

            return Response()->json([false, __('apps::dashboard.general.message_error')]);
        } catch (\Exception $e) {
            throw $e;
            return Response()->json([false, $e->errorInfo[2]]);
        }
    }

    public function switcher($id, $action)
    {
        try {
            $switch = $this->product->switcher($id, $action);
            if ($switch) {
                return Response()->json();
            }
            return Response()->json([false, __('apps::dashboard.general.message_error')]);
        } catch (\PDOException $e) {
            return Response()->json([false, $e->errorInfo[2]]);
        }
    }
}
