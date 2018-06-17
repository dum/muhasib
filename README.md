# Muhasib

Muhasib is a small Laravel/Lumen package to fetch and accumulate cryptocurrency price.
Now supports following exchanges:
 - Binance
 - Bittrex
 - Poloniex

### Usage
    <?php
        use Muhasib\Client;
    
        $client = new Client();
        // Set exchange (Binance, Bittrex, Poloniex)
        $client->setExchange($pair['exchange']);
        // Set pair
        $client->setPair([$left, $right]);
        // Get pair's price
        $client->getPrice();
        // Get history for current pair in hours
        $client->getHistory(0);
        // Get history for current pair in minutes
        $client->getHistory(1);
    ?>
    
### TODO
 - Add tests
 - Add more exchanges