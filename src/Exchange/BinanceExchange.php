<?php
namespace Muhasib\Exchange;

use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

class BinanceExchange extends BaseExchange {

    /**
     * @var string
     */
    public $delimiter = '';

    /**
     * @var string
     */
    public $exchange = 'binance';

    /**
     * @var string
     */
    private $priceUrl = 'https://api.binance.com/api/v3/ticker/price';

    /**
     * @var string
     */
    private $pairsUrl = 'https://api.binance.com/api/v1/exchangeInfo';

    /**
     * Get price from API
     *
     * @param null $pair
     * @return int|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getPriceFromAPI($pair = null)
    {
        try {
            $res = $this->http->request('GET', $this->priceUrl);
            $results = json_decode($res->getBody());
            $price = 0;
            foreach($results as $res) {
                Cache::tags([$this->exchange])->put($res->symbol, $res->price, $this->pricesExpire);
                if($pair && $res->symbol === $this->pair) $price = $res->price;
                $this->updateHistory($res->symbol, $res->price);
            }
            if($pair)
                return $price;
        } catch (RequestException $e) {
            echo Psr7\str($e->getRequest());
            if ($e->hasResponse()) {
                echo Psr7\str($e->getResponse());
            }
        }
        return 0;
    }

    /**
     * Get available pairs via API
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @return array
     */
    public function getPairsFromAPI()
    {
        try {
            $pairs = [];
            $res = $this->http->request('GET', $this->pairsUrl);
            $results = json_decode($res->getBody());
            foreach($results->symbols as $item) {
                $pairs[$item->baseAsset][] = $item->quoteAsset;
            }
            ksort($pairs);
            $this->pairs = $pairs;
            Cache::tags([$this->exchange])->put("pairs", $pairs, $this->pairsExpire);
            return $pairs;
        } catch (RequestException $e) {
            echo Psr7\str($e->getRequest());
            if ($e->hasResponse()) {
                echo Psr7\str($e->getResponse());
            }
        }
        return [];
    }
}