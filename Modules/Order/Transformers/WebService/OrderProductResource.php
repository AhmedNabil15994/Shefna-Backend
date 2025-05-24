<?php

namespace Modules\Order\Transformers\WebService;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Catalog\Transformers\WebService\ProductResource;
use Modules\Core\Traits\CoreTrait;
use Modules\Vendor\Traits\VendorTrait;

class OrderProductResource extends JsonResource
{
    use CoreTrait;
    use VendorTrait;
    public function toArray($request)
    {

        $delivery_charge_notes = null;
        $result = [
            'selling_price' => $this->price,
            'qty' => $this->qty,
            'total' => $this->total,
            'notes' => $this->notes,
            'order_notes' => $this->order_notes,
            'is_rated' => $this->checkUserRateOrder($this->id),
            'rate' => $this->getOrderRate($this->id),
        ];

        if (isset($this->product_variant_id) && !empty($this->product_variant_id)) {
            $prdTitle = '';
            foreach ($this->orderVariantValues as $k => $orderVal) {
                $prdTitle .= optional(optional(optional($orderVal->productVariantValue)->optionValue))->title . ' ,';
            }
            $result['title'] = $this->variant->product->title . ' - ' . rtrim($prdTitle, ' ,');
            $result['image'] = url($this->variant->image);
            $result['sku'] = $this->variant->sku;
            $deliveryCharge = $this->variant->product->vendor->deliveryCharge()->where('state_id',$this->variant->order->orderAddress->state_id)->first();
            if($deliveryCharge){
                $delivery_charge_notes = $deliveryCharge->delivery_time;
            }

            if (!empty($this->add_ons_option_ids))
                $result['addons'] = $this->buildOrderAddonsArray(json_decode($this->add_ons_option_ids, true));
            else
                $result['addons'] = [];
        } else {
            $result['title'] = $this->product->title;
            $result['image'] = url($this->product->image);
            $result['sku'] = $this->product->sku;
            $deliveryCharge = $this->product->vendor->deliveryCharge()->where('state_id',$this->order->orderAddress->state_id)->first();
            if($deliveryCharge){
                $delivery_charge_notes = $deliveryCharge->delivery_time;
            }

            if (!empty($this->add_ons_option_ids))
                $result['addons'] = $this->buildOrderAddonsArray(json_decode($this->add_ons_option_ids, true));
            else
                $result['addons'] = [];
        }
        $result['delivery_time_note'] = $delivery_charge_notes;


        return $result;
    }
}
