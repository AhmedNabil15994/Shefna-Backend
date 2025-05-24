<?php

namespace Modules\Catalog\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Traits\HasSlugTranslation;
use Modules\Core\Traits\ScopesTrait;
use Modules\Vendor\Entities\Vendor;
use Spatie\Translatable\HasTranslations;

class AddonCategory extends Model
{
    use HasTranslations, SoftDeletes, ScopesTrait;
    use HasSlugTranslation;

    protected $table = 'addon_categories';
    protected $guarded = ["id"];
    public $translatable = ['title', 'slug'];

    public function addonOptions()
    {
        return $this->hasMany(AddonOption::class, 'addon_category_id');
    }

    public function productAddons()
    {
        return $this->hasMany(ProductAddon::class, 'addon_category_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

}
