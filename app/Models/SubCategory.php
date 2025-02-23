<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'maincategory_id', 'image_url', 'image_path', 'icon_path', 'status', 'theme_id', 'store_id'
    ];

    protected $appends = ["icon_img_path","image_path_full_url","icon_path_full_url"];

    public function MainCategory()
    {
        return $this->hasOne(MainCategory::class, 'id', 'maincategory_id');
    }

    public function getIconImgPathAttribute($value)
    {
        $icon_path = 'themes/'.APP_THEME().'/upload/require/dot.png';
        if(!empty($this->icon_path)) {
            $icon_path = $this->icon_path;
        }
        return $icon_path;
    }

    public function getImagePathFullUrlAttribute() {
        $image = [];
        if(!empty($this->image_path)) {
            return get_file($this->image_path, $this->theme_id);
        }
        return $image;
    }

    public function getIconPathFullUrlAttribute() {
        $image = [];
        if(!empty($this->icon_path)) {
        	return get_file($this->icon_path, $this->theme_id);
		}
        return $image;
    }

    public function product_details() {
        return $this->hasMany(Product::class, 'subcategory_id', 'id');
    }

    public static function subCategoryImageDelete($subCategory)
    {
        if ($subCategory->image_path !== '/storage/uploads/default.jpg' && \File::exists(base_path($subCategory->image_path))) {
            Utility::changeStorageLimit(\Auth::user()->creatorId(), $subCategory->image_path );
        }

        if ($subCategory->icon_path !== '/storage/uploads/default.jpg' && \File::exists(base_path($subCategory->icon_path))) {
            Utility::changeStorageLimit(\Auth::user()->creatorId(), $subCategory->icon_path );
        }
    }
}
