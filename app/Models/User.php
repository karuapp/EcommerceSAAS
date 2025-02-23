<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Laratrust\Contracts\LaratrustUser;
use Laratrust\Traits\HasRolesAndPermissions;
use DB;
use Carbon\Carbon;
use Lab404\Impersonate\Models\Impersonate;
use Laravel\Paddle\Billable;
use Illuminate\Support\Facades\Cache;

class User extends Authenticatable implements LaratrustUser, MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable,HasRolesAndPermissions, Impersonate,Billable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_image',
        'type',
        'email_verified_at',
        'mobile',
        'register_type',
        'is_assign_store',
        'current_store',
        'language',
        'default_language',
        'plan_id',
        'plan_expire_date',
        'plan_is_active',
        'requested_plan',
        'storage_limit',
        'is_active',
        'created_by',
        'theme_id',
        'remember_token',
        'is_enable_login',
        'active_module'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public static $defalut_theme = [
        'grocery',
        'babycare',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function getProfileImageAttribute($value) {
        if (!empty($value)) {
            if(check_file('storage/'.$value)){
                return 'storage/'.$value;
            } else {
                return $value;
            }
        } else {
            return null;
        }

    }

    public function creatorId()
    {
        if($this->type == 'admin' || $this->type == 'super admin')
        {
            return $this->id;
        }
        else
        {
            return $this->created_by;
        }
    }

    public static function slugs($data)
    {
        $slug = '';
        
        // Remove special characters
        $slug = preg_replace('/[^a-zA-Z0-9\s-]/', '', $data);
        
        // Replace multiple spaces with a single hyphen
        $slug = preg_replace('/\s+/', '-', trim($slug));
        // Convert to lowercase
        $slug = strtolower($slug);

        $table = with(new Store)->getTable();
        $allSlugs = self::getRelatedSlugs($table, $slug ,$id = 0);

        if (!$allSlugs->contains('slug', $slug)) {
            return $slug;
        }
        for ($i = 1; $i <= 100; $i++) {
            $newSlug = $slug . '-' . $i;
            if (!$allSlugs->contains('slug', $newSlug)) {
                return $newSlug;

            }
        }
    }

    public function dateFormat($date)
    {
        $settings = Utility::seting();

        return date($settings['site_date_format'], strtotime($date));
    }


    protected static function getRelatedSlugs($table, $slug, $id = 0)
    {
        return DB::table($table)->select()->where('slug', 'like', $slug . '%')->where('id', '<>', $id)->get();
    }

    public function stores()
    {
        return $this->hasMany(Store::class, 'created_by', 'id');
    }

    public function countStore()
    {
        return Store::where('created_by', '=', $this->creatorId())->count();
    }
    public function assignPlan($planID)
    {
        $user = \Auth::user();
        $plan = Plan::find($planID);
        $oldplan= Plan::where('id',$this->plan_id)->first();
        if($plan)
        {
            $this->plan_id = $plan->id;
            if($this->trial_expire_date != null);
            {
                $this->trial_expire_date = null;
            }
            if($plan->duration == 'Month')
            {
                $this->plan_expire_date = Carbon::now()->addMonths(1)->isoFormat('YYYY-MM-DD');
            }
            elseif($plan->duration == 'Year')
            {
                $this->plan_expire_date = Carbon::now()->addYears(1)->isoFormat('YYYY-MM-DD');
            }
            else if($plan->duration == 'Unlimited')
            {
                if (isset($plan->trial_days) && !empty($plan->trial_days) && $plan->price == 0) {
                    $this->plan_expire_date = Carbon::now()->addDays($plan->trial_days)->isoFormat('YYYY-MM-DD');
                } else {
                    $this->plan_expire_date = null;
                }
            }

            $modules = $plan->modules ?? null;
            if(!empty($modules))
            {
                $modules_array = explode(',',$modules);
                $currentActiveModules = userActiveModule::where('user_id', $this->id)->pluck('module')->toArray();

                if(!empty($user->active_module) && $oldplan->custom_plan == 1)
                {
                    $user_module = $currentActiveModules;
                    foreach ($modules_array as $module) {
                        if(!in_array($module,$user_module)){
                            array_push($user_module,$module);
                        }
                    }
                }
                else
                {
                    $user_module = $modules_array;
                }


                // Sidebar Performance Changes
                $newModules = array_diff($user_module, $currentActiveModules);
                $modulesToRemove = array_diff($currentActiveModules, $user_module);

                foreach ($newModules as $moduleName) {
                    userActiveModule::create([
                        'user_id' => $this->id,
                        'module' => $moduleName,
                    ]);
                }

                foreach ($modulesToRemove as $moduleName) {
                    userActiveModule::where('user_id', $user->id)->where('module', $moduleName)->delete();
                }

                $this->active_module = implode(',',$user_module);

            }

            $this->save();
            $users    = User::where('created_by', '=', $this->id)->where('type', '!=', 'super admin')->get();
           
            if($plan->max_users == -1)
            {
                foreach($users as $user)
                {
                    $user->is_active = 1;
                    $user->save();
                }
            }
            else
            {
                $userCount = 0;
                foreach($users as $user)
                {
                    $userCount++;
                    if($userCount <= $plan->max_users)
                    {
                        $user->is_active = 1;
                        $user->save();
                    }
                    else
                    {
                        $user->is_active = 0;
                        $user->save();
                    }
                }
            }

            $stores    = Store::where('created_by', '=', $this->id)->get();

            if ($plan->max_stores == -1) {
                foreach ($stores as $store) {
                    $store->is_active = 1;
                    $store->save();
                }
            } else {
                $storeCount = 0;
                foreach ($stores as $store) {
                    $storeCount++;
                    if ($storeCount <= $plan->max_stores) {
                        $store->is_active = 1;
                        $store->save();
                    } else {
                        $store->is_active = 0;
                        $store->save();
                    }
                }
            }

            return ['is_success' => true];
        }
        else
        {
            return [
                'is_success' => false,
                'error' => 'Plan is deleted.',
            ];
        }
    }

    public function currentPlan()
    {
        return $this->hasOne(Plan::class, 'id', 'plan_id');
    }

    public function countProducts()
    {
        return Product::where('created_by', '=', $this->creatorId())->where('store_id', getCurrentStore())->count();
    }

    public function countCompany()
    {
        return User::where('type', '=', 'admin')->where('created_by', '=', $this->creatorId())->count();
    }

    // Relationship with users created by the user
    public function createdAdmins()
    {
        return $this->hasMany(User::class, 'created_by')->where('type', 'admin');
    }

    public static $superadmin_activated_module = [
        'LandingPage'
    ];

    public function totalStoreUser($id)
    {
        return User::where('created_by', '=', $id)->count();
    }

    public function totalStoreCustomer($id)
    {
        return Customer::where('store_id', '=', $id)->count();
    }

    public function totalStoreVender($id)
    {
        //return Vender::where('created_by', '=', $id)->count();
        return 0;
    }
}
