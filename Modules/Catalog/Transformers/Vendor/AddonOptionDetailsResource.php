<?php

namespace Modules\Catalog\Transformers\Vendor;

use Illuminate\Http\Resources\Json\JsonResource as Resource;

class AddonOptionDetailsResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->addonOption->id ?? '---',
            'title' => $this->addonOption->title ?? '---',
            'image' => optional($this->addonOption)->image ? url($this->addonOption->image) : null,
            'price' => $this->addonOption->price ?? null,
            'qty' => $this->addonOption->qty ?? '---',
            'default' => $this->default,
        ];
    }
}
