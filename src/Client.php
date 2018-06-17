<?php

namespace Muhasib;

use Muhasib\Exchange\BaseExchange;

/**
 * Client Class
 */
class Client
{

    /**
     * @var array $list Exchanges list
     */
    private $list = ["Binance", "Bittrex", "Poloniex"];

    /**
     * @var BaseExchange $exchange exchange config.
     */
    private $exchange;

    /**
     * Set exchange
     *
     * @param  string $exchange
     * @return Client
     */
    public function setExchange($exchange)
    {
        if(in_array($exchange, $this->list)) {
            $exchangeClass = "Muhasib\Exchange\\" . $exchange . 'Exchange';
            $this->exchange = new $exchangeClass;
        } else {
            //TODO exception Not correct Exchange
        }
        return $this;
    }

    /**
     * Set pair
     *
     * @param  array $pair
     * @return Client
     */
    public function setPair($pair)
    {
        $this->exchange->setPair($pair);
        return $this;
    }

    /**
     * Get price.
     *
     * @return float
     */
    public function getPrice()
    {
        $results = $this->exchange->getPrice();
        return $results;
    }

    /**
     * Get available pairs
     *
     * @return array
     */
    public function getPairs()
    {
        $results = $this->exchange->getPairs();
        return $results;
    }

    /**
     * Get history
     *
     * @param int $period
     * @return array
     */
    public function getHistory($period)
    {
        $results = $this->exchange->getHistory($period);
        return $results;
    }

}