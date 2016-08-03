<?php

namespace Omnipay\Stripe\Message;

use Omnipay\Tests\TestCase;

class PurchaseRequestTest extends TestCase
{
    /** @var PurchaseRequest $request */
    private $request;
    private $card;

    public function setUp()
    {
        $this->request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->card = $this->getValidCard();
        $this->request->initialize(
            array(
                'amount' => '10.00',
                'currency' => 'USD',
                'card' => $this->card,
                'statementDescriptor' => "FOO"
            )
        );
    }

    public function testShouldReturnDataAsAnArray()
    {
        $data = $this->request->getData();
        $expected = array(
            'amount' => 1000,
            'currency' => 'usd',
            'card' => array(
                'number' => '4111111111111111',
                'address_zip' => '12345',
                'cvc' => $this->card['cvv'],
                'exp_month' => $this->card['expiryMonth'],
                'exp_year' => $this->card['expiryYear'],
                'name' => 'Example User',
                'address_line1' => '123 Billing St',
                'address_line2' => 'Billsville',
                'address_city' => 'Billstown',
                'address_state' => 'CA',
                'address_country' => 'US'
            ),
            'statement_descriptor' => 'FOO',
            'description' => null,
            'capture' => 'true'
        );
        $this->assertEquals($expected, $data);
    }

    public function testShouldUseOlderKeyOfStatementDescriptorWhenApiVersionIsBeforeDec2014()
    {
        $this->request->setApiVersion('2014-06-17');

        $data = $this->request->getData();

        $this->assertFalse(isset($data['statement_descriptor']), 'statement_descriptor should not be used for older version of the API');
        $this->assertNotNull($data['statement_description'], 'statement_description should be used for this api version');
        $this->assertEquals('FOO', $data['statement_description']);
    }

    public function testCaptureIsTrue()
    {
        $data = $this->request->getData();
        $this->assertSame('true', $data['capture']);
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse('PurchaseSuccess.txt');
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('ch_1IU9gcUiNASROd', $response->getTransactionReference());
        $this->assertNull($response->getCardReference());
        $this->assertNull($response->getMessage());
    }

    public function testSendError()
    {
        $this->setMockHttpResponse('PurchaseFailure.txt');
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getTransactionReference());
        $this->assertNull($response->getCardReference());
        $this->assertSame('Your card was declined', $response->getMessage());
    }
}
