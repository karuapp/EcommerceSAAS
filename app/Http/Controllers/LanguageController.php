<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Cookie;
use Session;
use Auth;
use App\Models\Utility;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\App;
use App\Models\AddOnManager;
use App\Facades\ModuleFacade as Module;
use Illuminate\Support\Facades\Artisan;

class LanguageController extends Controller
{
    public function changeLanquageStore($lang = '')
    {
        Artisan::call('cache:clear');
        Cookie::queue('LANGUAGE',$lang, 1440);
        Session::put(['LANGUAGE' => $lang]);
        return redirect()->back()->with('success', __('Language change successfully.'));
    }

    public function changeLanquage($lang)
    {
        Artisan::call('cache:clear');
        // Cookie::queue('LANGUAGE',$lang, 1440);
        // Session::put(['LANGUAGE' => $lang]);
        $user = auth()->user();
        $user->language = $lang;
        $user->save();

        $settingQuery = Setting::query();
        if ($lang == 'ar' || $lang == 'he') {
            (clone $settingQuery)->updateOrCreate(
                [
                'name' => 'SITE_RTL',
                'theme_id' => APP_THEME(),
                'store_id' => getCurrentStore()
            ],
                [
                    'value'         => 'on',
                    'name'          => 'SITE_RTL',
                    'theme_id'      => APP_THEME(),
                    'store_id'      => getCurrentStore(),
                    'created_by'    => auth()->user()->id,
                ]
            );
        } else {
            (clone $settingQuery)->updateOrCreate(
                [
                'name' => 'SITE_RTL',
                'theme_id' => APP_THEME(),
                'store_id' => getCurrentStore()
            ],
                [
                    'value'         => 'off',
                    'name'          => 'SITE_RTL',
                    'theme_id'      => APP_THEME(),
                    'store_id'      => getCurrentStore(),
                    'created_by'    => auth()->user()->id,
                ]
            );
        }
        
        (clone $settingQuery)->updateOrCreate(
            [
            'name' => 'defult_language',
            'theme_id' => APP_THEME(),
            'store_id' => getCurrentStore()
        ],
            [
                'value'         => $lang,
                'name'          => 'defult_language',
                'theme_id'      => APP_THEME(),
                'store_id'      => getCurrentStore(),
                'created_by'    => auth()->user()->id,
            ]
        );
        // Set the application locale to the selected language
        App::setLocale($lang);

        return redirect()->back()->with('success', __('Language change successfully.'));
    }

    public function changelanguage($lang = '')
    {
        Artisan::call('cache:clear');
        Cookie::queue('LANGUAGE',$lang, 1440);
        Session::put(['LANGUAGE' => $lang]);
       
        if(Auth::check()){            
            $user       = Auth::user();
            $user->language = $lang;
            $user->save();
            $settingQuery = Setting::query();
            if ($lang == 'ar' || $lang == 'he') {
                (clone $settingQuery)->updateOrCreate(
                    [
                    'name' => 'SITE_RTL',
                    'theme_id' => APP_THEME(),
                    'store_id' => getCurrentStore()
                ],
                    [
                        'value'         => 'on',
                        'name'          => 'SITE_RTL',
                        'theme_id'      => APP_THEME(),
                        'store_id'      => getCurrentStore(),
                        'created_by'    => auth()->user()->id ?? 0,
                    ]
                );
            } else {
                (clone $settingQuery)->updateOrCreate(
                    [
                    'name' => 'SITE_RTL',
                    'theme_id' => APP_THEME(),
                    'store_id' => getCurrentStore()
                ],
                    [
                        'value'         => 'off',
                        'name'          => 'SITE_RTL',
                        'theme_id'      => APP_THEME(),
                        'store_id'      => getCurrentStore(),
                        'created_by'    => auth()->user()->id ?? 0,
                    ]
                );
            }
            
            (clone $settingQuery)->updateOrCreate(
                [
                'name' => 'defult_language',
                'theme_id' => APP_THEME(),
                'store_id' => getCurrentStore()
            ],
                [
                    'value'         => $lang,
                    'name'          => 'defult_language',
                    'theme_id'      => APP_THEME(),
                    'store_id'      => getCurrentStore(),
                    'created_by'    => auth()->user()->id ?? 0,
                ]
            );
        }
        // Set the application locale to the selected language
        //App::setLocale($lang);
        return redirect()->back()->with('success', __('Language change successfully.'));
    }

    public function manageLanguage($currantLang,$module='General')
    {
        if(auth()->user() && auth()->user()->isAbleTo('Manage Language'))
        {
            $languages = Utility::languages();
            $settings = Setting::pluck('value', 'name')->toArray();
            if (!empty($settings['disable_lang'])) {
                $disabledLang = explode(',', $settings['disable_lang']);
            } else {
                $disabledLang = [];
            }

            if($module == 'General' ){
                $dir = base_path() . '/resources/lang/' . $currantLang;
                $arrLabel   = json_decode(file_get_contents($dir . '.json'));
            }else{
                $module = AddOnManager::where('module', $module)->first();
                if($module)
                {
                    $module= $module->module;
                    $this_module = Module::find($module);
                    $path =   $this_module->getPath();
                    $dir_module = $path.'/src/resources/lang/' . $currantLang;
                    $arrLabel   = json_decode(file_get_contents($dir_module . '.json'));
                    $dir = base_path() . '/resources/lang/' . $currantLang;
                }else{
                    return redirect()->back()->with('error', __('Please active this module.'));
                }
            }

            if(!is_dir($dir))
            {
                $dir = base_path() . '/resources/lang/en';
            }

            $arrFiles   = array_diff(
                scandir($dir), array(
                                    '..',
                                    '.',
                                )
            );
            $arrMessage = [];

            foreach($arrFiles as $file)
            {
                $fileName = basename($file, ".php");
                $fileData = $myArray = include $dir . "/" . $file;
                if(is_array($fileData))
                {
                    $arrMessage[$fileName] = $fileData;
                }
            }
            return view('language.index', compact('languages', 'currantLang', 'arrLabel', 'arrMessage','disabledLang','module'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function storeLanguageData(Request $request, $currantLang, $module = 'General')
    {
        if(auth()->user() && auth()->user()->isAbleTo('Create Language'))
        {
            $Filesystem = new Filesystem();
            $dir        = base_path() . '/resources/lang';
            if($module == 'General' ){
                $dir = base_path() . '/resources/lang/';
                if(!is_dir($dir))
                {
                    mkdir($dir);
                    chmod($dir, 0777);
                }
                $jsonFile = $dir . "/" . $currantLang . ".json";
            }else{
                $modules = AddOnManager::where('module',$module)->first();
                if(!empty($modules))
                {
                    $this_module = Module::find($modules->module);
                    $path =   $this_module->getPath();
                    $dir_module = $path.'/src/resources/lang/';
                    if(!is_dir($dir_module))
                    {
                        mkdir($dir_module);
                        chmod($dir_module, 0777);
                    }
                    $jsonFile = $dir_module . "/" . $currantLang . ".json";
                    $dir = base_path() . '/resources/lang/';
                    if(!is_dir($dir))
                    {
                        mkdir($dir);
                        chmod($dir, 0777);
                    }
                }else{
                    return redirect()->back()->with('error', __('Please active this module.'));
                }
            }

            if(isset($request->label) && !empty($request->label))
            {
                file_put_contents($jsonFile, json_encode($request->label));
            }

            $langFolder = $dir . "/" . $currantLang;

            if(!is_dir($langFolder))
            {
                mkdir($langFolder);
                chmod($langFolder, 0777);
            }
            if(isset($request->message) && !empty($request->message))
            {
                foreach($request->message as $fileName => $fileData)
                {
                    $content = "<?php return [";
                    $content .= $this->buildArray($fileData);
                    $content .= "];";
                    file_put_contents($langFolder . "/" . $fileName . '.php', $content);
                }
            }

            return redirect()->route('manage.language', [$currantLang])->with('success', __('Language save successfully.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function disableLang(Request $request)
    {
        $settingQuery = Setting::query();
        if (\Auth::user()->type == 'super admin') {
            $theme_name = !empty(APP_THEME()) ? APP_THEME() : env('DATA_INSERT_APP_THEME');
            $settings = Setting::where('theme_id', $theme_name)->where('store_id', getCurrentStore())->pluck('value', 'name')->toArray();
            // $disablelang  = '';
            if ($request->mode == 'off') {
                if (!empty($settings['disable_lang'])) {
                    $disablelang = $settings['disable_lang'];
                    $disablelang = $disablelang . ',' . $request->lang;
                } else {
                    $disablelang = $request->lang;
                }
                
                    (clone $settingQuery)->updateOrCreate(
                        [
                            'name' => 'disable_lang',
                            'theme_id' => APP_THEME(),
                            'store_id' => getCurrentStore()
                        ],
                        [
                            'value'         => $disablelang,
                            'name'          => 'disable_lang',
                            'theme_id'      => APP_THEME(),
                            'store_id'      => getCurrentStore(auth()->user()->id, null),
                            'created_by'    => auth()->user()->id,
                        ]
                    );

                $data['message'] = __('Language Disabled Successfully');
                $data['status'] = 200;
                return $data;
            } else {
                $disablelang = $settings['disable_lang'];
                $parts = explode(',', $disablelang);
                while (($i = array_search($request->lang, $parts)) !== false) {
                    unset($parts[$i]);
                }
                (clone $settingQuery)->updateOrCreate(
                    [
                        'name' => 'disable_lang',
                        'theme_id' => APP_THEME(),
                        'store_id' => getCurrentStore()
                    ],
                    [
                        'value'         => implode(',', $parts),
                        'name'          => 'disable_lang',
                        'theme_id'      => APP_THEME(),
                        'store_id'      => getCurrentStore(auth()->user()->id, null),
                        'created_by'    => auth()->user()->id,
                    ]
                );
                
                $data['message'] = __('Language Enabled Successfully');
                $data['status'] = 200;
                return $data;
            }
        }
    }

    public function createLanguage()
    {
        return view('language.create');
    }

    public function storeLanguage(Request $request)
    {
        
        if(auth()->user() && auth()->user()->isAbleTo('Create Language'))
        {               
            $Filesystem = new Filesystem();
            $langCode   = strtolower($request->code);
            $langDir    = base_path() . '/resources/lang/';
            $dir        = $langDir;
            if(!is_dir($dir))
            {
                mkdir($dir);
                chmod($dir, 0777);
            }
            $dir      = $dir . '/' . $langCode;
            $jsonFile = $dir . ".json";
            \File::copy($langDir . 'en.json', $jsonFile);

            if(!is_dir($dir))
            {
                mkdir($dir);
                chmod($dir, 0777);
            }
            $Filesystem->copyDirectory($langDir . "en", $dir . "/");

            // Specify the path to your JSON file
            $filePath = base_path('resources/lang/language.json');

            // Read the existing JSON file and decode its contents into an array
            $jsonContents = File::get($filePath);
            $data = json_decode($jsonContents, true);

            //append key wise data
            $data[$request->code] = $request->name;

            // Encode the updated array back to JSON format
            $jsonContentsUpdated = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

            // Write the JSON data back to the file, preserving the existing contents
            File::put($filePath, $jsonContentsUpdated);


            return redirect()->route('manage.language', [$langCode])->with('success', __('Language successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroyLang($lang)
    {        
        if(auth()->user() && auth()->user()->isAbleTo('Delete Language'))
        {
            $default_lang = env('default_language') ?? 'en';
            $langDir      = base_path() . '/resources/lang/';
            if(is_dir($langDir))
            {
                // remove directory and file
                Utility::delete_directory($langDir . $lang);

                // Specify the path to your JSON file
                $filePath = base_path('resources/lang/language.json');

                // Read the contents of the existing JSON file and decode it into an array
                $jsonContents = File::get($filePath);
                $data = json_decode($jsonContents, true);

                // Remove the data based on the key
                $keyToRemove = $lang;
                unset($data[$keyToRemove]);

                // Encode the updated array back to JSON format
                $jsonContentsUpdated = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

                // Write the JSON data back to the file, replacing the existing contents
                File::put($filePath, $jsonContentsUpdated);


                unlink($langDir . $lang . '.json');
                // update user that has assign deleted language.
                User::where('language', 'LIKE', $lang)->update(['language' => $default_lang]);
            }
            return redirect()->route('manage.language', $default_lang)->with('success', __('Language Deleted Successfully.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
