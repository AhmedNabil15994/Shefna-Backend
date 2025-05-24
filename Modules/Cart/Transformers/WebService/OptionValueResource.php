<?php

namespace Modules\Cart\Transformers\WebService;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Catalog\Entities\Product;
use Modules\Catalog\Transformers\WebService\ProductOptionResource;
use Modules\Catalog\Transformers\WebService\ProductVariantResource;
use Modules\Variation\Entities\ProductVariant;

class OptionValueResource extends JsonResource
{
    public function toArray($request)
    {
//        dd($this->resource->addonOption);
       return [
           'id' => $this->id ,
           'title' => $this->title
       ];
    }
}
