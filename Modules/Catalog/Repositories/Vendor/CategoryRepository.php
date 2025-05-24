<?php

namespace Modules\Catalog\Repositories\Vendor;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Modules\Catalog\Entities\Category;
use Modules\Core\Traits\CoreTrait;

class CategoryRepository
{
    use CoreTrait;

    protected $category;

    public function __construct(Category $category)
    {
        $this->category = $category;
    }

    public function getAll($order = 'id', $sort = 'desc')
    {
        return $this->category->orderBy($order, $sort)->get();
    }

    public function getAllActive($order = 'id', $sort = 'desc')
    {
        return $this->category->active()->orderBy($order, $sort)->get();
    }

    public function mainCategories($order = 'sort', $sort = 'asc')
    {
        $categories = $this->category->with('children')->mainCategories()->orderBy($order, $sort)->get();
        return $categories;
    }

    public function findById($id)
    {
        $category = $this->category->withDeleted()->find($id);
        return $category;
    }

}
