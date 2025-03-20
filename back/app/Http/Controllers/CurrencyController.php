<?php

namespace App\Http\Controllers;

use App\Exceptions\GeneralException;
use App\Http\Controllers\Base\BaseController;
use App\Http\Controllers\Base\BaseControllerFirefly;
use App\Utils\CurrencyUtils;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class CurrencyController extends BaseControllerFirefly
{

    public function __construct()
    {
        parent::__construct("/api/v1/currencies");
    }

    // ---------------------------


    public function exchangeRates()
    {
        if (!getUser()) {
            throw new GeneralException("Unauthorized", BaseController::HTTP_CODE_UNAUTHORIZED);
        }

        $date = Carbon::now()->startOfDay()->format("Y-m-d");
        $cacheKey = "exchange_$date";
        return Cache::remember($cacheKey, 60 * 60 * 24 * 5, function () {
            $response = Http::get("https://api.fxratesapi.com/latest"); //Alternative: https://open.er-api.com/v6/latest/USD
            if ($response->status() !== self::HTTP_CODE_OK) {
                return null;
            }

            $body = $response->json();
            $rates = fget($body, 'rates');

            return [
                'date' => Carbon::parse(fget($body, 'date'))->startOfDay()->format("Y-m-d"),
                'rates' => $rates,
                'currencies' => CurrencyUtils::CURRENCIES
            ];
        });
    }


}