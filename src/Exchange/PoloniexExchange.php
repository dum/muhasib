<?php
namespace Muhasib\Exchange;

use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;


class PoloniexExchange extends BaseExchange {

    /**
     * @var string
     */
    public $delimiter = '_';

    /**
     * @var string
     */
    public $exchange = 'poloniex';

    /**
     * @var string
     */
    private $priceUrl = 'https://poloniex.com/public?command=returnTicker';

    /**
     * @var string
     */
    private $pairsUrl = 'https://poloniex.com/public?command=returnTicker';

    /**
     * Get price from API
     *
     * @param null $pair
     * @return float
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getPriceFromAPI($pair = null)
    {
        try {
            $res = $this->http->request('GET', $this->priceUrl);
            $results = json_decode($res->getBody());
            foreach($results as $pair => $item) {
                Cache::tags([$this->exchange])->put($pair, $item->last, $this->pricesExpire);
                $this->updateHistory($pair, $item->last);
            }
            if($this->pair) {
                $pair = $this->pair;
                $price = $results->$pair->last;
                return $price;
            }
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
            foreach($results as $pair => $item) {
                $pair = explode($this->delimiter, $pair);
                $pairs[$pair[0]][] = $pair[1];
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