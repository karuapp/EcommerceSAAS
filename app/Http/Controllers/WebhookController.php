<?php

namespace App\Http\Controllers;

use App\Models\Webhook;
use Illuminate\Http\Request;
use App\Models\Store;
use Illuminate\Support\Facades\Cache;

class WebhookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('webhook.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        session()->put(['setting_tab' => 'webhook_setting']);
        $store = Cache::remember('store_' . getCurrentStore(), 3600, function () {
            return Store::find(getCurrentStore());
        });

        $validator = \Validator::make(
            $request->all(),
            [
                'module' => 'required|unique:webhooks,module,NULL,id,store_id,' . $store->id . ',theme_id,' . $store->theme_id,
                'method' => 'required',
                'webbbook_url' => 'required',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $webhook            = new Webhook();
        $webhook->module    = $request->module;
        $webhook->url       = $request->webbbook_url;
        $webhook->method    = $request->method;
        $webhook->store_id  = $store->id;
        $webhook->theme_id  = $store->theme_id;
        $webhook->save();

        return redirect()->back()->with('success', __('Webhook setting created successfully'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Webhook $webhook)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $webhook = Webhook::where('id', $id)->where('store_id', getCurrentStore())->get();
        return view('webhook.edit', compact('webhook'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        session()->put(['setting_tab' => 'webhook_setting']);
        $store = Cache::remember('store_' . getCurrentStore(), 3600, function () {
            return Store::find(getCurrentStore());
        });

        $validator = \Validator::make(
            $request->all(),
            [
                'module' => 'required|unique:webhooks,module,' . $id . ',id,store_id,' . $store->id . ',theme_id,' . $store->theme_id,
                'method' => 'required',
                'webbbook_url' => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $webhook['module']      =   $request->module;
        $webhook['method']      =   $request->method;
        $webhook['url']         =   $request->webbbook_url;
        $webhook['store_id']    =   $store->id;
        Webhook::where('id', $id)->update($webhook);

        return redirect()->back()->with('success', __('Webhook Setting Succssfully Updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        session()->put(['setting_tab' => 'webhook_setting']);
        if (auth()->user() && auth()->user()->type == 'admin') {
            $webhook = Webhook::find($id);
            if ($webhook) {
                $webhook->delete();
                return redirect()->back()->with('success', __('Webhook Setting successfully deleted .'));
            } else {
                return redirect()->back()->with('error', __('Something is wrong.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
