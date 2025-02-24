<?php

namespace CryptaTech\Seat\JanicePriceProvider\PriceProvider;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use JsonException;
use CryptaTech\Seat\JanicePriceProvider\JanicePriceProviderServiceProvider;
use RecursiveTree\Seat\PricesCore\Contracts\IPriceable;
use RecursiveTree\Seat\PricesCore\Contracts\IPriceProviderBackend;
use RecursiveTree\Seat\PricesCore\Exceptions\PriceProviderException;
use RecursiveTree\Seat\PricesCore\Utils\UserAgentBuilder;

class JanicePriceProvider implements IPriceProviderBackend
{

    /**
     * Fetches the prices for the items in $items
     * Implementations should store the computed price directly on the Priceable object using the setPrice method.
     * In case an error occurs, a PriceProviderException should be thrown, so that an error message can be shown to the user.
     *
     * @param Collection<IPriceable> $items The items to appraise
     * @param array $configuration The configuration of this price provider backend.
     * @throws PriceProviderException
     */
    public function getPrices(Collection $items, array $configuration): void
    {
        // step 1: Collect TypeIDs we are interested in, if we have a cached entry use it straight away.

        $cacheprefix = 'janice_pricer_' . $configuration['id'];

        $typeIDs = [];
        $typeIDFetch = [];
        foreach ($items as $item) {
            $price = Cache::tags([$cacheprefix])->get($cacheprefix . $item->getTypeID());
            if (isset($price)) {
                $typeIDs[$item->getTypeID()] = floatval($price);
            } else {
                $typeIDFetch[] = $item->getTypeID();
            }
        }

        // dd($typeIDFetch, $typeIDs);

        // step 2: Request prices for those we still need.
        if (count($typeIDFetch) > 0) {
            $user_agent = (new UserAgentBuilder())
                ->seatPlugin(JanicePriceProviderServiceProvider::class)
                ->defaultComments()
                ->build();

            $client = new \GuzzleHttp\Client([
                'base_uri' => "https://janice.e-351.com/",
                'timeout' => $configuration['timeout'],
                'headers' => [
                    'User-Agent' => $user_agent,
                    'X-ApiKey' => $configuration['apikey'],
                    'Content-Type' => 'text/plain',
                ]
            ]);

            try {
                $response = $client->post('api/rest/v2/pricer', [
                    'query' => [
                        'market' => $configuration['region'],
                    ],
                    'body' => implode("\n", $typeIDFetch),
                    // 'debug' => true,
                ]);
                // dd(str($response->getBody()));
                $response = json_decode($response->getBody(), false, 64, JSON_THROW_ON_ERROR);
            } catch (GuzzleException | JsonException $e) {
                // dd($e, implode("\n", $typeIDFetch));
                throw new PriceProviderException('Failed to load data from janice', 0, $e);
            }
            
            foreach ($response as $item) {
                if ($configuration['immediate'] == 'immediate') {
                    $price_bucket = $item->immediatePrices;
                } else {
                    $price_bucket = $item->top5AveragePrices;
                }

                $variant = $configuration['price_type'];
                if ($variant == 'buy') {
                    $price = $price_bucket->buyPrice;
                } elseif ($variant == 'split') {
                    $price = $price_bucket->splitPrice;
                } elseif ($variant == 'sell') {
                    $price = $price_bucket->sellPrice;
                } elseif ($variant == 'buy5') {
                    $price = $price_bucket->buyPrice5DayMedian;
                } elseif ($variant == 'split5') {
                    $price = $price_bucket->splitPrice5DayMedian;
                } elseif ($variant == 'sell5') {
                    $price = $price_bucket->sellPrice5DayMedian;
                } elseif ($variant == 'buy30') {
                    $price = $price_bucket->buyPrice30DayMedian;
                } elseif ($variant == 'split30') {
                    $price = $price_bucket->splitPric30DayMedian;
                } elseif ($variant == 'sell30') {
                    $price = $price_bucket->sellPrice30DayMedian;
                } else {
                    $price = $price_bucket->split;
                }

                $typeIDs[$item->itemType->eid] = floatval($price);
                Cache::tags([$cacheprefix])->put($item->itemType->eid, $price, now()->addHours($configuration['cache']));
            }
        }
        // step 3: Feed prices back to system
        foreach ($items as $item) {
            $price = $typeIDs[$item->getTypeID()] ?? null;
            if ($price === null) {
                if ($configuration['allow_empty'] == 'yes') {
                    $item->setPrice(0);
                    continue;
                }
                throw new PriceProviderException('Janice didn\'t respond with the requested prices.');
            }
            if (!(is_int($price) || is_float($price))) {
                throw new PriceProviderException('Janice responded with a non-numerical price: "' . $price . '". (' . gettype($price) . ').');
            }

            $item->setPrice($price * $item->getAmount());
        }

        // dd($items);
    }
}
