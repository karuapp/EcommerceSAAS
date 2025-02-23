<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Plan;
use App\Models\Store;
use App\Models\Utility;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\DataTables\UserDataTable;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(auth()->user() && auth()->user()->isAbleTo('Manage User'))
        {
            $users = User::where('created_by','=',\Auth::user()->creatorId())->where('current_store', getCurrentStore())->paginate(11);

            return view('users.index',compact('users'));

        }
        else{
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (auth()->user() && auth()->user()->isAbleTo('Create User'))
        {
            $user  = \Auth::user();
            $roles = Role::where('created_by', '=', $user->creatorId())->get()->pluck('name', 'id');
            return view('users.create',compact('roles'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (auth()->user() && auth()->user()->isAbleTo('Create User'))
        {
            $exitUser = User::where('email', $request->email)->first();
            if ($exitUser && $exitUser->created_by != auth()->user()->id) {
                return redirect()->back()->with('error', __('The email is already being used in another account. please choose another email'));
            }
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'email' => [
                        'required',
                        Rule::unique('users')->where(function ($query) {
                        return $query->where('created_by', \Auth::user()->id);
                        })
                    ],
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $user = \Auth::user();
            $creator = User::find($user->creatorId());
            $total_users = User::where('type', '!=', 'super admin')->where('type', '!=', 'admin')->where('created_by', '=', $user->id)->count();
            $plan = Cache::remember('plan_details_'.$user->id, 3600, function () use ($user) {
                return Plan::find($user->plan_id);
            });
            // $plan = '5';

            if ($total_users < $plan->max_users || $plan->max_users == -1)
            {

                $objUser    = \Auth::user();
                $role_r = Role::find($request->role);

                $user =  new User();
                $user->name =  $request['name'];
                $user->email =  $request['email'];
                $user->type = $role_r->name;
                $user->password = Hash::make($request['password']);
                $user->is_assign_store = $objUser->current_store;
                $user->language = $objUser->default_language ?? 'en';
                $user->default_language = $objUser->default_language ?? 'en';
                $user->created_by = \Auth::user()->id;
                $user->email_verified_at = date("Y-m-d H:i:s");
                $user->current_store = $objUser->current_store;
                $user->plan_id = $objUser->plan;
                $user->is_active = 1;

                if (module_is_active('GoogleAuthentication')) {
                    $user->google2fa_enable = $objUser->google2fa_enable ;
                    $user->google2fa_secret = $objUser->google2fa_secret;
                }

                $user->save();

                $user->addRole($role_r);
                // webhook
                if(!empty($user))
                {
                    $module = 'New User';
                    $store = Cache::remember('store_' . getCurrentStore(), 3600, function () {
                        return Store::find(getCurrentStore());
                    });
                    $webhook =  Utility::webhook($module, $store->id);

                    if ($webhook) {
                        $storeDetail=Store::find($user->current_store);
                        $user->current_store=$storeDetail->name;
                        $user->is_assign_store = $storeDetail->name;
                        $parameter = json_encode($user);

                        // 1 parameter is  URL , 2 parameter is data , 3 parameter is method
                        $status = Utility::WebhookCall($webhook['url'], $parameter, $webhook['method']);
                        if ($status != true) {
                            $msgs = 'Webhook call failed.';
                        }
                    }
                    return redirect()->back()->with('success', __('User successfully created.' . (isset($msgs) ? '<br><span class="text-danger">' . $msgs . '</span>' : '')));
                }
                return redirect()->back()->with('success', 'User successfully created.');
            } else {
                return redirect()->back()->with('error', __('Your User limit is over, Please upgrade plan'));
            }
        }
        else{
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (auth()->user() && auth()->user()->isAbleTo('Edit User'))
        {
            $user  = User::find($id);
            $roles = Role::where('created_by', '=', auth()->user()->creatorId())->get()->pluck('name', 'id');
            return view('users.edit', compact('user', 'roles'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        if (auth()->user() && auth()->user()->isAbleTo('Edit User'))
        {
            $user = User::findOrFail($id);

            $exitUser = User::where('email', $request->email)->where('id', '!=', $id)->first();
            if ($exitUser && $exitUser->created_by != auth()->user()->id) {
                return redirect()->back()->with('error', __('The email is already being used in another account. please choose another email'));
            }

            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'email' => ['required',
                                Rule::unique('users')->where(function ($query)  use ($user) {
                                return $query->whereNotIn('id',[$user->id])->where('created_by',  \Auth::user()->creatorId())->where('current_store', getCurrentStore());
                            })
                    ],
                ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }


            $role          = Role::find($request->role);
            $input         = $request->all();
            if ($role) {
                $input['type'] = $role->name;
            }

            $user->fill($input)->save();

            if ($role && !$user->hasRole($role->name)) {
                $user->addRole($role);
            }
            return redirect()->back()->with('success', 'User successfully updated.');
        }
        else{
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {

        if (auth()->user() && auth()->user()->isAbleTo('Delete User'))
        {
            Setting::where('created_by', $user->id)->delete();
            $user->delete();

            return redirect()->back()->with('success', 'User successfully deleted.');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function reset($id)
    {
        if (auth()->user() && auth()->user()->isAbleTo('Reset Password'))
        {
            $Id        = \Crypt::decrypt($id);
            $user = User::find($Id);
            $employee = User::where('id', $Id)->first();

            return view('users.reset', compact('user', 'employee'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function updatePassword(Request $request, $id)
    {
        if (auth()->user() && auth()->user()->isAbleTo('Reset Password'))
        {
            $validator = \Validator::make(
                $request->all(),
                [
                    'password' => 'required|confirmed|same:password_confirmation',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $user                 = User::where('id', $id)->first();
            $user->forceFill([
                'password' => Hash::make($request->password),
            ])->save();

            return redirect()->back()->with( 'success', __('User Password successfully updated.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function profile()
    {
        $userDetail = \Auth::user();
        return view('users.profile', compact('userDetail'));
    }

    public function editprofile(Request $request)
    {
        $userDetail = \Auth::guard()->user();
        // $dir        = Storage::url('uploads/profile/');
        $dir        = 'profile/';
        $rule['name'] = 'required';
        $rule['email'] = 'required';

        $validator = \Validator::make($request->all(), $rule);
        if($validator->fails())
        {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        if ($request->hasFile('profile_image')) {
            $fileName = rand(10,100).'_'.time() . "_" . $request->profile_image->getClientOriginalName();
            $path = Utility::upload_file($request,'profile_image',$fileName,$dir,[]);
            if ($path['flag'] == '0') {
                return redirect()->back()->with('error', $path['msg']);
            }
        }

        $user_id = \Auth::guard()->user()->id;
        $user               = User::Where('id', $user_id)->first();
        if (!empty($request->profile_image) && isset($path['url'])) {
            $user['profile_image'] = str_replace('/storage', '', $path['url']);
        }
        $user->name   = $request->name;
        $user->email        = $request->email;
        $user->mobile        = $request->mobile;

        $user->save();

        return redirect()->back()->with('success', __('Personal info successfully updated.'));
    }

    public function password_update(Request $request, $slug = '')
    {
        $store = Cache::remember('store_' . $slug, 3600, function () use ($slug) {
                return Store::where('slug',$slug)->first();
            });
        if (!empty($store)) {
            $theme_id = $store->theme_id;
        }

        # Validation
        $rule['old_password'] = 'required';
        $rule['new_password'] = 'required|confirmed';

        $validator = \Validator::make($request->all(), $rule);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        if (!empty($request->type) && ($request->type = 'admin' || $request->type = 'superadmin')) {
            #Match The Old Password
            if (!Hash::check($request->old_password, Auth::guard()->user()->password)) {
                return redirect()->back()->with('error', __("Old Password Does not match!"));
            }

            #Update the new Password
            User::whereId(Auth::guard()->user()->id)->update([
                'password' => Hash::make($request->new_password)
            ]);
        } else {
            #Match The Old Password
            if (!Hash::check($request->old_password, auth()->user()->password)) {
                return redirect()->back()->with('error', __("Old Password Does not match!"));
            }

            #Update the new Password
            User::whereId(auth()->user()->id)->update([
                'password' => Hash::make($request->new_password)
            ]);
        }


        return redirect()->back()->with('success', __('Password update succefully.'));
    }

    public function userInfo(Request $request, $id)
	{
        if(empty($id)){
            $id = $request->id;
        }
		if(!empty($id)){
            $user = User::find($request->id);
            $status = $user->is_active;
		    $data = $this->storeUserCounter($id);
		    if($data['is_success']){
		        $users_data = $data['response']['users_data'];
		        $store_data = $data['response']['store_data'];
		        return view('users.user_info', compact('id','users_data','store_data','status'));
		    }
		}
		else
		{
		    return redirect()->back()->with('error', __('Permission denied.'));
		}
    }

    public function storeUserCounter($id)
    {
		$response = [];
		if(!empty($id))
		{
		    $stors= Store::where('created_by', $id)
		    ->selectRaw('COUNT(*) as total_store, SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as disable_store, SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_store')
		    ->first();
		    $stores = Store::where('created_by',$id)->get();
		    $users_data = [];
		    foreach($stores as $store)
		    {
		        $users = User::where('type','!=','admin')->where('created_by',$id)->where('current_store',$store->id)->selectRaw('COUNT(*) as total_users, SUM(CASE WHEN is_enable_login = 0 THEN 1 ELSE 0 END) as disable_users, SUM(CASE WHEN is_enable_login = 1 THEN 1 ELSE 0 END) as active_users')->first();

		        $users_data[$store->name] = [
		            'store_id' => $store->id,
		            'total_users' => !empty($users->total_users) ? $users->total_users : 0,
		            'disable_users' => !empty($users->disable_users) ? $users->disable_users : 0,
		            'active_users' => !empty($users->active_users) ? $users->active_users : 0,
		        ];
		    }
		    $store_data =[
		        'total_store' =>  $stors->total_store,
		        'disable_store' => $stors->disable_store,
		        'active_store' => $stors->active_store,
		    ];

		    $response['users_data'] = $users_data;
		    $response['store_data'] = $store_data;

		    return [
		        'is_success' => true,
		        'response' => $response,
		    ];
		}
		return [
		    'is_success' => false,
		    'error' => __( 'Plan is deleted.'),
		];
    }

    public function UserUnable(Request $request)
    {
		if(!empty($request->id) && !empty($request->owner_id))
		{
		    if($request->name == 'user')
		    {
		        User::where('id', $request->id)->update(['is_enable_login' => $request->is_active]);
		        $data = $this->storeUserCounter($request->owner_id);

		    }
		    elseif($request->name == 'store')
		    {
                $enabled_stores = Store::where('created_by', $request->owner_id)
                    ->where('is_active', 1)
                    ->count();
                if($request->is_active == 0){
                    if($enabled_stores != 1){

                        Store::where('id',$request->id)->update(['is_active' => $request->is_active]);

                        User::where('current_store',$request->id)->where('type','!=','admin')->update(['is_enable_login' => $request->is_active]);
                        
                        $stores_enabled = Store::where('created_by', $request->owner_id)->where('is_active', 1)->first();
                        User::where('id', $request->owner_id)->update(['current_store' => $stores_enabled->id]);
                    }else{
                        return response()->json(['error' => __('All Store can not disable. At least One store must be enabled.')]);
                    }
                }else{
                    Store::where('id',$request->id)->update(['is_active' => $request->is_active]);
                }
                $data = $this->storeUserCounter($request->owner_id);
		    }
		    if($data['is_success'])
		    {
		        $users_data = $data['response']['users_data'];
		        $store_data = $data['response']['store_data'];
		    }
		    if($request->is_active == 1){

		        return response()->json(['success' => __('Successfully Unable.'),'users_data' => $users_data, 'store_data' => $store_data]);
		    }else
		    {
		        return response()->json(['success' => __('Successfull Disable.'),'users_data' => $users_data, 'store_data' => $store_data]);
		    }
		}
        return response()->json(['error' => __('Something went wrong'),'users_data' => null, 'store_data' => null]);
    }

    public function userLoginManage($id)
    {
        $eId = \Crypt::decrypt($id);
        $user = User::find($eId);
        if ($user->is_enable_login == 1) {
            $user->is_enable_login = 0;
            $message = __('User login disable successfully.');
        } else {
            $user->is_enable_login = 1;
            $message = __('User login enable successfully.');
        }
        $user->save();

        return redirect()->back()->with('success', $message);
    }

    public function list(Request $request, UserDataTable $dataTable)
    {
        if(auth()->user() && auth()->user()->isAbleTo('Manage User')) {
            return $dataTable->render('users.list');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
