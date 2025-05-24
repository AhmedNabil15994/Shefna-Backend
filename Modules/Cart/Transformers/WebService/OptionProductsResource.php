<?php

namespace Modules\Cart\Transformers\WebService;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Catalog\Entities\Product;
use Modules\Catalog\Transformers\WebService\ProductOptionResource;
use Modules\Catalog\Transformers\WebService\ProductVariantResource;
use Modules\Variation\Entities\ProductVariant;

class OptionProductsResource extends JsonResource
{


    public function toArray($request)
    {



       return [
             'id' => $this->id ?? null,
             'title' => $this->addonsOptions->title ?? null,
             'option_value' => [
                 OptionValueResource::make($this->addonOption)
             ]
       ];
    }
}
