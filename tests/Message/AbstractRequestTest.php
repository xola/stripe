<?php

namespace Omnipay\Stripe\Message;

use Mockery;
use Omnipay\Tests\TestCase;

class AbstractRequestTest extends TestCase
{
    public function setUp()
    {
        $this->request = Mockery::mock('\Omnipay\Stripe\Message\AbstractRequest')->makePartial();
        $this->request->initialize();
    }

    public function testCardReference()
    {
        $this->assertSame($this->request, $this->request->setCardReference('abc123'));
        $this->assertSame('abc123', $this->request->getCardReference());
    }

    public function testCardToken()
    {
        $this->assertSame($this->request, $this->request->setToken('abc123'));
        $this->assertSame('abc123', $this->request->getToken());
    }

    public function testSource()
    {
        $this->assertSame($this->request, $this->request->setSource('abc123'));
        $this->assertSame('abc123', $this->request->getSource());
    }

    public function testCardData()
    {
        $card = $this->getValidCard();
        $this->request->setCard($card);
        $data = $this->request->getCardData();

        $this->assertSame($card['number'], $data['number']);
        $this->assertSame($card['cvv'], $data['cvc']);
    }

    public function testCardDataEmptyCvv()
    {
        $card = $this->getValidCard();
        $card['cvv'] = '';
        $this->request->setCard($card);
        $data = $this->request->getCardData();

        $this->assertTrue(empty($data['cvv']));
    }

    public function testMetadata()
    {
        $this->assertSame($this->request, $this->request->setMetadata(array('foo' => 'bar')));
        $this->assertSame(array('foo' => 'bar'), $this->request->getMetadata());
    }

    /*
    public function testSendDataSetsApiVersionIfPresent()
    {
        $apiVersion = '2014-10-12';

        $eventDispatcher = Mockery::mock('\Symfony\Component\EventDispatcher\EventDispatcherInterface')
            ->shouldReceive('addListener')->getMock();

        $response = Mockery::mock('\Guzzle\Http\Message\Response')->shouldReceive('json')->getMock();

        $request = $this->getMockRequest()
            ->shouldReceive('setHeader')->once()->withArgs(array('Authorization', 'Basic Og=='))
            ->shouldReceive('setHeader')->once()->withArgs(array('Stripe-Version', $apiVersion))
            ->shouldReceive('send')->andReturn($response)
            ->getMock();

        $httpClient = Mockery::mock('Guzzle\Http\ClientInterface')
            ->shouldReceive('getEventDispatcher')->andReturn($eventDispatcher)
            ->shouldReceive('createRequest')->andReturn($request)
            ->getMock();

        $request = Mockery::mock('\Omnipay\Stripe\Message\AbstractRequest[getEndPoint,getApiVersion]', array($httpClient, $this->getHttpRequest()));
        $request->shouldReceive('getApiVersion')->andReturn($apiVersion);
        $request->shouldReceive('getEndpoint')->andReturn('foo');

        $request->sendData(array('foo' => 'bar'));
    }
    */
}
