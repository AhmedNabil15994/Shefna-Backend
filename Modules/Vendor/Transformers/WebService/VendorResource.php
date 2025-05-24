<?php

namespace Modules\Vendor\Transformers\WebService;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Vendor\Traits\VendorTrait;

class VendorResource extends JsonResource
{
    use VendorTrait;

    public function toArray($request)
    {
        $result = [
            'id' => $this->id,
            'image' => $this->image ? url($this->image) : null,
            'cover' => $this->cover ? url($this->cover) : null,
            'title' => $this->title,
            'description' => $this->description,
            'rate' => $this->getVendorRate($this->id),
            'address' => $this->address ?? null,
            'recently_joined' => $this->recently_joined ,
            'mobile' => !is_null($this->mobile) ? /*$this->calling_code .*/$this->mobile : null,

            /*'payments' => PaymenteResource::collection($this->payments),
            'fixed_delivery' => $this->fixed_delivery,
            'order_limit' => $this->order_limit,
            'rate' => $this->getVendorTotalRate($this->rates),*/

            'preparation_time' => $this->preparation_time,
        ];

        $result['opening_status'] = $this->checkVendorBusyStatus($this->id);
        if ($request->with_vendor_categories == 'yes') {
            $result['vendor_categories'] = CategoryResource::collection($this->categories);
        }
        $day = lcfirst(\Carbon\Carbon::parse(now())->locale('en')->shortDayName);
//        $result['delivery_times'] = $this->deliveryTimes()->get(['id','day_code','custom_times']);
        $result['today_delivery_times'] = $this->deliveryTimes()->where('day_code',$day)->get(['id','day_code','custom_times']);
        if(count($result['today_delivery_times'])){
            $result['today_delivery_times'] = $result['today_delivery_times'][0];
            if(isset($result['today_delivery_times']['custom_times'])){
                $result['today_delivery_times']['times'] = $result['today_delivery_times']['custom_times'];
                unset($result['today_delivery_times']['custom_times']);
            }
        }else{
            $result['today_delivery_times'] = null;
        }

//        $result['today_delivery_times'] = reset($result['today_delivery_times']);
        return $result;
    }
}
