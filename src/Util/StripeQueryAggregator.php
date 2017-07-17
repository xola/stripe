<?php

namespace Omnipay\Stripe\Util;

use \Guzzle\Http\QueryAggregator\QueryAggregatorInterface;
use \Guzzle\Http\QueryString;

class StripeQueryAggregator implements QueryAggregatorInterface
{

    /**
     * There is no standard way specified for sending array values for a query string. If a query sting 'foo' has an
     * array value [bar, baz], this can normally be sent in different ways. However Stripe accepts only the format
     * `foo[]=bar&foo[]=baz' which is not supported by any of the query aggregators provided by Guzzle. This function
     * aggregates nested query string variables using [] but without the array index. For example, if a query param with
     * key 'foo' has an array value [bar, baz], this function returns ['foo[]' => ['bar', 'baz']]
     * The query param will be sent in any request as: http://test.com?foo[]=bar&foo[]=baz
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
