<?php

namespace Omnipay\Stripe\Message;

use Mockery as m;
use Omnipay\Tests\TestCase;

class AbstractRequestTest extends TestCase
{
    public function testSendDataSetsApiVersionIfPresent_Mockery()
    {
        $apiVersion = '2014-10-12';

        $eventDispatcher = m::mock('\Symfony\Component\EventDispatcher\EventDispatcherInterface')
            ->shouldReceive('addListener')->getMock();

        $response = m::mock('\Guzzle\Http\Message\Response')->shouldReceive('json')->getMock();

        $request = $this->getMockRequest()
            ->shouldReceive('setHeader')->once()->withArgs(array('Authorization', 'Basic Og=='))
            ->shouldReceive('setHeader')->once()->withArgs(array('Stripe-Version', $apiVersion))
            ->shouldReceive('send')->andReturn($response)
            ->getMock();

        $httpClient = m::mock('Guzzle\Http\ClientInterface')
            ->shouldReceive('getEventDispatcher')->andReturn($eventDispatcher)
            ->shouldReceive('createRequest')->andReturn($request)
            ->getMock();

        $request = m::mock('\Omnipay\Stripe\Message\AbstractRequest[getEndPoint,getApiVersion]', array($httpClient, $this->getHttpRequest()));
        $request->shouldReceive('getApiVersion')->andReturn($apiVersion);
        $request->shouldReceive('getEndpoint')->andReturn('foo');

        $request->sendData(array('foo' => 'bar'));
    }

}