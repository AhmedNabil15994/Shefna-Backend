<?php

namespace Modules\Catalog\ViewComposers\Dashboard;

use Illuminate\View\View;
use Modules\Catalog\Entities\AddonOption;
use Modules\Catalog\Entities\Product;
use Modules\Catalog\Repositories\Dashboard\AddonCategoryRepository as AddonCategory;

class AddonCategoryComposer
{
    public $sharedActiveAddonCategories;

    public function __construct(AddonCategory $addonCategory)
    {
        $vendorId = null;
//        if (request()->route()->getName() == 'dashboard.products.add_ons') {
//            $vendorId = Product::find(request()->id)->vendor_id ?? null;
//        } elseif (request()->route()->getName() == 'dashboard.addon_options.edit') {
//            $vendorId = AddonOption::with('addonCategory')->find(request()->id)->addonCategory->vendor_id ?? null;
//        }
        $this->sharedActiveAddonCategories = $addonCategory->getAllActive('sort', 'asc', $vendorId);
    }

    /**
     * Bind data to the view.
     *
     * @param View $view
     * @return void
     */
    public function compose(View $view)
    {
        $view->with(['sharedActiveAddonCategories' => $this->sharedActiveAddonCategories]);
    }
}
