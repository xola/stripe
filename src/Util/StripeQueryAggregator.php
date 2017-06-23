<?php

namespace Omnipay\Stripe\Util;

use \Guzzle\Http\QueryAggregator\QueryAggregatorInterface;
use \Guzzle\Http\QueryString;

class StripeQueryAggregator implements QueryAggregatorInterface
{

    /**
     * Aggregates nested query string variables using [] but without the array index.
     * Example: http://test.com?foo[]=1&foo[]=2
     *
     * @param string $key The name of the query string parameter
     * @param array $value The values of the parameter
     * @param QueryString $query The query string that is being aggregated
     *
     * @return array Returns an array of the combined values
     */
    public function aggregate($key, $value, QueryString $query)
    {
        if ($query->isUrlEncoding()) {
            return array($query->encodeValue($key) . '[]' => array_map(array($query, 'encodeValue'), $value));
        } else {
            return array($key . '[]' => $value);
        }
    }
}
