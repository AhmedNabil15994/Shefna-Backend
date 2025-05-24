<?php

namespace Modules\Cart\Transformers\WebService;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Catalog\Entities\Product;
use Modules\Catalog\Transformers\WebService\ProductOptionResource;
use Modules\Catalog\Transformers\WebService\ProductVariantResource;
use Modules\Variation\Entities\ProductVariant;

class CartResource extends JsonResource
{
    public function toArray($request)
    {
        $data = [];
        if ($this->attributes->addonsOptions != null) {
            $options = collect($this->attributes->addonsOptions['data'])->pluck('options')->toArray();
            foreach ($this->attributes->product->addOns as $item) {
                foreach ($options as $idOptions) {

                    $data['data'] = $item->addonOptions->whereIn('addon_option_id', $idOptions);
                }
            }
        }




//       $this->attributes->product->addOns  ;






        $result = [
            'id' => (string)$this->id,
            'qty' => $this->quantity,
            'image' => url($this->attributes->product->image),
            'product_type' => $this->attributes->product->product_type,
            'notes' => $this->attributes->notes,
            'preparation_time_product' => $this->attributes->product->preparation_time_product ,
            'time_add' => Carbon::now()
        ];

        if ($this->attributes->product->product_type == 'product') {
            $result['title'] = $this->attributes->product->title;
            $currentProduct = Product::find($this->attributes->product->id);
            // $result['remaining_qty'] = intval($currentProduct->qty) - intval($this->quantity);
        } else {
            $result['title'] = $this->attributes->product->product->title;
//            $result['product_options'] = CartProductOptionsResource::collection($this->attributes->product->productValues);
            $currentProduct = ProductVariant::find($this->attributes->product->id);
            // $result['remaining_qty'] = intval($currentProduct->qty) - intval($this->quantity);
        }

        if ($currentProduct) {
            if (!is_null($currentProduct->qty)) {
                $result['remaining_qty'] = intval($currentProduct->qty);
            } else {
                $result['remaining_qty'] = null;
            }
        } else {
            $result['remaining_qty'] = 0;
        }
        if ($this->attributes->addonsOptions) {
//            $price = floatval($this->price) - floatval($this->attributes->addonsOptions['total_amount']);
            $price = floatval($this->attributes->addonsOptions['total_amount'] * $this->quantity);
            $result['price'] = number_format($price, 3);
        } else
            $result['price'] = number_format($this->price, 3);

        $finalAtrr = $this->attributes->product->addOns ;
//            dd($finalAtrr->addonOption , $options);
//            $result['addons'] =  ;




        if (empty($data))
        {
            $result['product_options'] =null;
        }
        else{
            $result['product_options'] =
                OptionProductsResource::collection($data['data'])
            ;
        }

//

        return $result;
    }
}
