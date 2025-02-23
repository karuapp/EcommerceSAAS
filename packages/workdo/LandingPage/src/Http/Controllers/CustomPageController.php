<?php

namespace Workdo\LandingPage\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Workdo\LandingPage\Entities\LandingPageSetting;
use Workdo\LandingPage\Entities\OwnerMenu;
use Workdo\LandingPage\Entities\OwnerMenuSetting;

class CustomPageController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        if(\Auth::user()->type == 'super admin'){
            $settings = LandingPageSetting::settings();
            $pages = json_decode($settings['menubar_page'], true);
            $menus = OwnerMenu::select('id','name')->where('created_by',\Auth::user()->id)->get();
            $menus = OwnerMenu::select('id','name')->where('created_by',\Auth::user()->id)->get();
            $menusettings = OwnerMenuSetting::where('created_by',\Auth::user()->id)->first();
            if ($menusettings && isset($menusettings->menus_id)) {
                $get_menu = explode(',', $menusettings->menus_id);
            } else {
                $get_menu = [];
            }


            return view('landing-page::landingpage.menubar.index', compact('pages', 'settings','menus','menusettings','get_menu'));
        }else{
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('landing-page::landingpage.menubar.create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {



        $settings = LandingPageSetting::settings();
        $data = json_decode($settings['menubar_page'], true);
        $page_slug = str_replace(' ', '_', strtolower($request->menubar_page_name));

        $datas['menubar_page_name'] = $request->menubar_page_name;
        $datas['menubar_page_contant'] = $request->menubar_page_contant;
        $datas['page_slug'] = $page_slug;
        $datas['template_name'] = $request->template_name;

        if (isset($request->template_name) && $request->template_name == 'page_url') {
            $datas['page_url'] = $request->page_url;
            $datas['menubar_page_contant'] = '';
        } else {
            $datas['page_url'] = '';
            $datas['menubar_page_contant'] = $request->menubar_page_contant;
        }
        if($request->login){
            $datas['login'] = 'on';
        }else{
            $datas['login'] = 'off';
        }

        if($request->header){
            $datas['header'] = 'on';
        }else{
            $datas['header'] = 'off';
        }

        if($request->footer){
            $datas['footer'] = 'on';
        }else{
            $datas['footer'] = 'off';
        }



        $data[] = $datas;
        $data = json_encode($data);
        LandingPageSetting::updateOrCreate(['name' =>  'menubar_page'],['value' => $data]);

        return redirect()->back()->with(['success'=> __('Settings save successfully.')]);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('landing-page::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($key)
    {

        $settings = LandingPageSetting::settings();
        $pages = json_decode($settings['menubar_page'], true);
        $page = $pages[$key];
        return view('landing-page::landingpage.menubar.edit', compact('page', 'key'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $key)
    {
        $settings = LandingPageSetting::settings();
        $data = json_decode($settings['menubar_page'], true);
        $page_slug = str_replace(' ', '_', strtolower($request->menubar_page_name));
        $datas['menubar_page_name'] = $request->menubar_page_name;
        $datas['menubar_page_contant'] = $request->menubar_page_contant;

        $datas['page_slug'] = $page_slug;
        $datas['template_name'] = $request->template_name;

        if (isset($request->template_name) && $request->template_name == 'page_url') {
            $datas['page_url'] = $request->page_url;
            $datas['menubar_page_contant'] = '';
        } else {
            $datas['page_url'] = '';
            $datas['menubar_page_contant'] = $request->menubar_page_contant;
        }

        if ($request->login) {
            $datas['login'] = 'on';
        } else {
            $datas['login'] = 'off';
        }


        if($request->header){
            $datas['header'] = 'on';
        }else{
            $datas['header'] = 'off';
        }

        if($request->footer){
            $datas['footer'] = 'on';
        }else{
            $datas['footer'] = 'off';
        }

        $data[$key] = $datas;
        $data = json_encode($data);


        LandingPageSetting::updateOrCreate(['name' =>  'menubar_page'],['value' => $data]);
        return redirect()->back()->with(['success'=> __('Page Updated successfully')]);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($key)
    {
        $settings = LandingPageSetting::settings();
        $pages = json_decode($settings['menubar_page'], true);
        unset($pages[$key]);
        LandingPageSetting::updateOrCreate(['name' =>  'menubar_page'],['value' => $pages]);

        return redirect()->back()->with(['success'=> 'Page deleted successfully']);
    }


    public function customStore(Request $request)
    {
        if( $request->site_logo){
            $site_logo = "site_logo." . $request->site_logo->getClientOriginalExtension();
            $dir        = 'uploads/landing_page_image/';
            $path = LandingPageSetting::upload_file($request,'site_logo',$site_logo,$dir,[]);
            if($path['flag']==0){
                return redirect()->back()->with('error', __($path['msg']));
            }
            $data['site_logo'] = $path['url']??$site_logo;
        }

        $data['site_description'] = $request->site_description;

        foreach($data as $key => $value){

            LandingPageSetting::updateOrCreate(['name' =>  $key],['value' => $value]);
        }

        return redirect()->back()->with(['success'=> __('Settings save successfully.')]);
    }


    public function customPage($slug)
    {
        $settings = LandingPageSetting::settings();
        $pages = json_decode($settings['menubar_page'], true);

        foreach ($pages as $key => $page) {
            if($page['page_slug'] == $slug){
                return view('landing-page::layouts.custompage', compact('page', 'settings'));
            }
        }

    }


}
