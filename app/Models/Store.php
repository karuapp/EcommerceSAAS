<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Store extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'theme_id',
        'slug',
        'default_language',
        'created_by',
        'is_active',
        'enable_pwa_store',
        'duo_setting_enabled',
        'duo_api_host_name',
        'duo_secret_key',
        'duo_integration_key'
    ];

    public static function pwa_store($slug)
    {
        $store = Cache::remember('store_' . $slug, 3600, function () use ($slug) {
                return Store::where('slug',$slug)->first();
            });
        try {

            $pwa_data = \File::get(storage_path('uploads/customer_app/store_' . $store->id . '/manifest.json'));

            $pwa_data = json_decode($pwa_data);
        } catch (\Throwable $th) {
            $pwa_data = [];
        }
        return $pwa_data;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
