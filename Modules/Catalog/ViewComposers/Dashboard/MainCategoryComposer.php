<?php

namespace Modules\Catalog\ViewComposers\Dashboard;

use Illuminate\View\View;
use Modules\Catalog\Repositories\Dashboard\CategoryRepository as Category;

class MainCategoryComposer
{
    public $mainCategories;

    public function __construct(Category $category)
    {
        $this->mainCategories = $category->mainCategories();
    }

    /**
     * Bind data to the view.
     *
     * @param View $view
     * @return void
     */
    public function compose(View $view)
    {
        $view->with(['mainCategories' => $this->mainCategories]);
    }
}
