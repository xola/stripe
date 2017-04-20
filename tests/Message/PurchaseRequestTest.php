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
            'source' => array(
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
                'address_country' => 'US',
                'object' => 'card',
                'email' => null
            ),
            'statement_descriptor' => 'FOO',
            'description' => null,
            'capture' => 'true',
            'metadata' => null
        );
        $this->assertEquals($expected, $data);
    }

    public function testShouldReturnLevel3DataIfPresent()
    {
        $level3Data = array(
            'merchant_reference' => '1245',
            'shipping_amount' => 0,
            'line_items' => array(
                'product_code' => '123',
                'product_description' => 'Experience title',
                'unit_cost' => 1000,
                'quantity' => 1,
                'tax_amount' => 100,
                'discount_amount' => 0,
            )
        );
        $this->request->initialize(
            array(
                'amount' => '10.00',
                'currency' => 'USD',
                'card' => $this->card,
                'statementDescriptor' => "FOO",
                'level3' => $level3Data
            )
        );
        $expected = array(
            'amount' => 1000,
            'currency' => 'usd',
            'source' => array(
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
                'address_country' => 'US',
                'object' => 'card',
                'email' => null
            ),
            'statement_descriptor' => 'FOO',
            'description' => null,
            'capture' => 'true',
            'metadata' => null,
            'level3' => $level3Data
        );

        $data = $this->request->getData();

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
        $this->assertSame('card_16n3EU2baUhq7QENSrstkoN0', $response->getCardReference());
        $this->assertNull($response->getMessage());
    }

    public function testSendWithSourceSuccess()
    {
        $this->setMockHttpResponse('PurchaseWithSourceSuccess.txt');
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('ch_1IU9gcUiNASROd', $response->getTransactionReference());
        $this->assertSame('card_15WgqxIobxWFFmzdk5V9z3g9', $response->getCardReference());
        $this->assertNull($response->getMessage());
    }

    public function testSendError()
    {
        $this->setMockHttpResponse('PurchaseFailure.txt');
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('ch_1IUAZQWFYrPooM', $response->getTransactionReference());
        $this->assertNull($response->getCardReference());
        $this->assertSame('Your card was declined', $response->getMessage());
    }
}
