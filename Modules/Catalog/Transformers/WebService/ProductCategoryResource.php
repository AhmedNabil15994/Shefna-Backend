<?php

namespace Modules\Catalog\Transformers\WebService;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductCategoryResource extends JsonResource
{
    public function toArray($request)
    {
        $response = [
            'id' => $this->id,
            'title' => $this->title,
            'products' => ProductResource::collection($this->products->where('vendor_id' , $request->vendor_id))
        ];

        if (request()->get('model_flag') == 'tree') {
            $response['sub_categories'] = ProductCategoryResource::collection($this->childrenRecursive);
        }

        return $response;
    }
}
