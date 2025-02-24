<?php

namespace CryptaTech\Seat\JanicePriceProvider\Http\Controllers;

use Illuminate\Http\Request;
use RecursiveTree\Seat\PricesCore\Models\PriceProviderInstance;
use Illuminate\Support\Facades\Cache;
use Seat\Web\Http\Controllers\Controller;

class JanicePriceProviderController extends Controller
{
    public function configuration(Request $request){
        $existing = PriceProviderInstance::find($request->id);

        $name = $request->name ?? $existing->name;
        $id = $request->id;
        $apikey = $existing->configuration['apikey'] ?? "";
        $immediate = $existing->configuration['immediate'] ?? 'immediate';
        $region = $existing->configuration['region'] ?? 10000002;
        $price_type = $existing->configuration['price_type'] ?? 'split';
        $cache = $existing->configuration['cache'] ?? 12;
        $timeout = $existing->configuration['timeout'] ?? 5;
        $allow_empty = $existing->configuration['allow_empty'] ?? 'no';

        return view('janicepriceprovider::configuration', compact(['id',  'name', 'apikey', 'immediate', 'region', 'price_type', 'cache', 'timeout', 'allow_empty']));
    }

    public function configurationPost(Request $request) {
        $request->validate([
            'id'=>'nullable|integer',
            'name'=>'required|string',
            'apikey'=>'required|string',
            'immediate'=>'required|string',
            'region'=>'required|integer',
            'price_type' => 'required|string|in:sell,sell5,sell30,buy,buy5,buy30,split,split5,split30',
            'cache'=>'required|integer|min:1|max:24',
            'timeout'=>'required|integer',
            'allow_empty'=>'required|string|in:yes,no',
        ]);

        $model = PriceProviderInstance::findOrNew($request->id);
        $model->name = $request->name;
        $model->backend = 'cryptatech/seat-prices-janice';
        $model->configuration = [
            'id' => $request->id,
            'apikey' => $request->apikey,
            'immediate' => $request->immediate,
            'region' => $request->region,
            'price_type' => $request->price_type,
            'cache' => $request->cache,
            'timeout' => $request->timeout,
            'allow_empty' => $request->allow_empty,
        ];
        $model->save();

        Cache::tags(['janice_pricer_' . $request->id ])->flush();

        return redirect()->route('pricescore::settings')->with('success',trans('janicepriceprovider::janice.edit_price_provider_success'));
    }
}