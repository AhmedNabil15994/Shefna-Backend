<?php

namespace Modules\Catalog\Transformers\WebService;

use Illuminate\Http\Resources\Json\JsonResource;

class AddonOptionsResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => optional($this->addonOption)->id ?? null,
            'option' => optional($this->addonOption)->getTranslation('title', locale()) ?? '---',
            'price' => number_format(optional($this->addonOption)->price, 3),
            'qty' => optional($this->addonOption)->qty,
            'image' => !is_null(optional($this->addonOption)->image) ? url(optional($this->addonOption)->image) : null,
            'default' => $this->default ? 1 : 0,
        ];
    }
}
