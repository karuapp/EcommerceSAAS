<?php

namespace App\Exports;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\Cache;

class CouponExport implements FromCollection, WithHeadings {




   public function headings(): array {
        return [
            "Number",
            "Coupon Name",
            "Coupon Code",
            "Coupon Type",
            "Applied Product",
            "Exclude Product",
            "Applied Categories",
            "Exclude Categories",
            "Minimum Spend",
            "Maximum Spend",
            "Coupon Limit User",
            "Coupon Limit x_item",
            "Coupon Limit",
            "Coupon Expiry Date",
            "Discount Amount",
            "Sale Items",
            "Free Shipping Coupon",
            "Status",
        ];
    }

    public function collection(){
        $store = Cache::remember('store_' . getCurrentStore(), 3600, function () {
            return Store::where('id', getCurrentStore())->first();
        });
        $data = Coupon::where('store_id', $store->id)->where('theme_id',APP_THEME())->get();
        $i = 1;
        foreach($data as $k => $coupon){
            unset($coupon->theme_id,$coupon->store_id ,$coupon->created_at,$coupon->updated_at);
            $data[$k]["id"] = $i++ ;
        }
        return collect($data);
    }

}
