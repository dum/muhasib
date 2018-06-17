<?php

namespace Muhasib\Jobs;

use App\Jobs\Job;
use Muhasib\Exchange\BinanceExchange;
use Muhasib\Exchange\BittrexExchange;
use Muhasib\Exchange\PoloniexExchange;

class UpdatePriceJob extends Job
{

    /**
     * Run job
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle()
    {
        $this->updateBinance();
        $this->updateBittrex();
        $this->updatePoloniex();
    }

    /**
     * Update Binance
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @return void
     */
    private function updateBinance()
    {
        $binance = new BinanceExchange();
        $binance->getPriceFromAPI();
    }

    /**
     * Update Bittrex
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @return void
     */
    private function updateBittrex()
    {
        $bittrex = new BittrexExchange();

        $pairsArray = $bittrex->getPairs();
        foreach($pairsArray as $pairs) {
            $first = $pairs['title'];
            foreach($pairs['pairs'] as $second) {
                $bittrex->setPair([$first, $second]);
                $bittrex->getPriceFromAPI();
            }
        }
    }

    /**
     * Update Poloniex
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @return void
     */
    private function updatePoloniex()
    {
        $poloniex = new PoloniexExchange();
        $poloniex->getPriceFromAPI();
    }
}