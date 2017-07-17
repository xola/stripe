<?php
use Guzzle\Http\QueryString;
use Omnipay\Tests\TestCase;
use \Omnipay\Stripe\Util\StripeQueryAggregator;

class StripeQueryAggregatorTest extends TestCase
{
    /** @var  StripeQueryAggregator */
    private $aggregator;

    public function setUp()
    {
        $this->aggregator = new StripeQueryAggregator();
    }

    public function testShouldAggregateArrayQueryParamsWithoutIndex()
    {
        $expected = array('foo[]' => array('bar', 'baz'));

        $actual = $this->aggregator->aggregate('foo', array('bar', 'baz'), new QueryString());

        $this->assertEquals($expected, $actual);
    }
}