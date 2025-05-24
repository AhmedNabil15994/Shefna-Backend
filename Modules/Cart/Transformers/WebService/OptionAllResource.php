<?php

namespace Modules\Cart\Transformers\WebService;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Catalog\Entities\Product;
use Modules\Catalog\Transformers\WebService\ProductOptionResource;
use Modules\Catalog\Transformers\WebService\ProductVariantResource;
use Modules\Variation\Entities\ProductVariant;

class OptionAllResource extends JsonResource
{


    public function toArray($request)
    {




//        return  $this ;

       return [

       ];
    }
}
