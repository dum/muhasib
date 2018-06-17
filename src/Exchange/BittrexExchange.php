<?php
namespace Muhasib\Exchange;

use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;


class BittrexExchange extends BaseExchange {

    /**
     * @var string
     */
    public $delimiter = '-';

    /**
     * @var string
     */
    public $exchange = 'bittrex';

    /**
     * @var string
     */
    private $priceUrl = 'https://bittrex.com/api/v1.1/public/getticker';

    /**
     * @var string
     */
    private $pairsUrl = 'https://bittrex.com/api/v1.1/public/getmarkets';

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
            $res = $this->http->request('GET', $this->priceUrl, ['query' => ['market' => $this->pair]]);
            $results = json_decode($res->getBody());
            $price = 0;
            if($results->success) {
                Cache::tags([$this->exchange])->put($this->pair, $results->result->Last, $this->pricesExpire);
                $price = $results->result->Last;
                $this->updateHistory($this->pair, $price);
            }
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
            foreach($results->result as $item) {
                $pairs[$item->BaseCurrency][] = $item->MarketCurrency;
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