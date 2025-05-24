<?php

namespace Modules\Catalog\Transformers\Vendor;

use Illuminate\Http\Resources\Json\JsonResource as Resource;

class AddonCategoryResource extends Resource
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
            'id' => $this->id,
            'title' => $this->title,
            'vendor' => $this->vendor->title ?? '---',
            'sort' => $this->sort,
            'deleted_at' => $this->deleted_at,
            'created_at' => date('d-m-Y', strtotime($this->created_at)),
        ];
    }
}
