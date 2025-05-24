<?php

namespace Modules\Catalog\Transformers\Dashboard;

use Illuminate\Http\Resources\Json\JsonResource as Resource;

class AddonDetailsResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $response = [
            'id' => $this->id,
            'addon_category_title' => optional($this->addonCategory)->title ?? '---',
            'type' => $this->type,
            'min_options_count' => $this->min_options_count,
            'max_options_count' => $this->max_options_count,
            'is_required' => $this->is_required,
            'created_at' => date('d-m-Y', strtotime($this->created_at)),
        ];

        if (!is_null($this->addonOptions)) {
            $response['addonOptions'] = AddonOptionDetailsResource::collection($this->addonOptions);
        } else {
            $response['addonOptions'] = null;
        }

        return $response;
    }
}
