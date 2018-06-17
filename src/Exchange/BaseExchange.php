<?php
namespace Muhasib\Exchange;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

abstract class BaseExchange {

    /**
     * @var string
     */
    public $delimiter;

    /**
     * @var string
     */
    public $exchange;

    /**
     * @var Client Http
     */
    public $http;

    /**
     * @var string
     */
    public $pair;

    /**
     * @var array
     */
    public $pairs;

    /**
     * @var int
     */
    public $pricesExpire = 1;

    /**
     * @var int
     */
    public $pairsExpire = 1440;

    /**
     * @var array
     */
    private $periods = ['hours', 'minutes'];

    /**
     * BaseExchange constructor.
     */
    public function __construct()
    {
        $this->http = new Client();
        $this->pairs = [];
    }

    /**
     * Set current pair
     *
     * @param array $pair
     * @return $this
     */
    public function setPair($pair)
    {
        $this->pair = implode($pair, $this->delimiter);
        return $this;
    }

    /**
     * Get price from Cache
     *
     * @return float
     */
    public function getPrice()
    {
        $price = Cache::tags([$this->exchange])->get($this->pair, function () {
            return $this->getPriceFromAPI($this->pair);
        });
        return $price;
    }

    /**
     * Get price from exchange's API
     *
     * @param string $pair
     * @return mixed
     */
    abstract function getPriceFromAPI($pair);


    /**
     * Update history
     *
     * @param $pair
     * @param $price
     */
    public function updateHistory($pair, $price)
    {
        //Hours update
        $hoursQuery = implode("_", [$pair, $this->periods[0]]);
        $hoursHistory = Cache::tags([$this->exchange])->get($hoursQuery);
        if(is_null($hoursHistory)) $hoursHistory = [];
        if(
            date('i') === '00' &&
            (count($hoursHistory) === 0 ||
                (count($hoursHistory) > 0 &&
                    ($hoursHistory[0]['time'] / 60 % 60) !== intval(date('i'))
                ))
        ) {
            array_unshift($hoursHistory, [
                'time'  => time(),
                'price' => $price
            ]);
            if(count($hoursHistory) > 24) array_splice($hoursHistory, 24, 1);

            Cache::tags([$this->exchange])->forever($hoursQuery, $hoursHistory);
        }

        //Minutes update
        $minutesQuery = implode("_", [$pair, $this->periods[1]]);
        $minutesHistory = Cache::tags([$this->exchange])->get($minutesQuery);

        if(is_null($minutesHistory)) $minutesHistory = [];
        if(
            count($minutesHistory) === 0 || (count($minutesHistory) > 0 &&
            ($minutesHistory[0]['time'] / 60 % 60) !== intval(date('i')))
        ) {
            array_unshift($minutesHistory, [
                'time' => time(),
                'price' => $price
            ]);
            if (count($minutesHistory) > 60) array_splice($minutesHistory, 60, 1);
            Cache::tags([$this->exchange])->forever($minutesQuery, $minutesHistory);
        }

    }

    /**
     * Get available pairs from Cache
     *
     * @return array
     */
    public function getPairs()
    {
        $pairsTemp = Cache::tags([$this->exchange])->get("pairs", function () {
            return $this->getPairsFromAPI();
        });
        $pairs = [];
        foreach($pairsTemp as $title => $pairsArray) {
            $pairs[] = [
                'title' => $title,
                'pairs' => $pairsArray
            ];
        }
        return $pairs;
    }

    /**
     * Get pairs from exchange's API
     *
     * @return mixed
     */
    abstract function getPairsFromAPI();

    /**
     * Get history
     *
     * @param int $period
     * @return array
     */
    public function getHistory($period)
    {
        $history = Cache::tags([$this->exchange])
            ->get(implode("_",[$this->pair, $this->periods[$period]]));
        return (is_null($history)) ? [] : $history;
    }
}