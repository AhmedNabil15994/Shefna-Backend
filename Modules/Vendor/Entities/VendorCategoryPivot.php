<?php

namespace Modules\Vendor\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Vendor\Entities\Vendor;
use \Modules\Vendor\Entities\VendorCategory;

class VendorCategoryPivot extends Model
{
    protected $table = 'vendor_categories_pivot';

    public function category()
    {
        return $this->belongsTo(VendorCategory::class, 'vendor_category_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class,'vendor_id');
    }
}
