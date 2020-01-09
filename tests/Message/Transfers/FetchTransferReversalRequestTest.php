<?php

namespace Omnipay\Stripe\Message\Transfers;

use Omnipay\Stripe\Message\Response;
use Omnipay\Tests\TestCase;
use function GuzzleHttp\Psr7\parse_response;

class FetchTransferReversalRequestTest extends TestCase
{
    /**
     * @var FetchTransferReversalRequest
     */
    protected $request;

    /**
     * @var string
     */
    protected $mockDir;

    public function setUp()
    {
        $this->mockDir = __DIR__.'/../../Mock/Transfers';
        $this->request = new FetchTransferReversalRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setTransferReference('tr_1ARKPl2eZvKYlo2CsNTKWIOO');
        $this->request->setReversalReference('trr_1ARKQ22eZvKYlo2Cv5APdtKF');
    }

    public function testEndpoint()
    {
        $this->assertSame(
            'https://api.stripe.com/v1/transfers/tr_1ARKPl2eZvKYlo2CsNTKWIOO/reversals/trr_1ARKQ22eZvKYlo2Cv5APdtKF',
            $this->request->getEndpoint()
        );
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse(
            array(parse_response(file_get_contents($this->mockDir.'/FetchTransferReversalSuccess.txt')))
        );

        /** @var Response $response */
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertSame('trr_1ARKQ22eZvKYlo2Cv5APdtKF', $response->getTransferReversalReference());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getMessage());
    }

    public function testSendFailure()
    {
        $this->setMockHttpResponse(
            array(parse_response(file_get_contents($this->mockDir.'/FetchTransferReversalFailure.txt')))
        );
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('No such transfer reversal: trr_1ARKQ22eZvKYlo2Cv5APdtKF', $response->getMessage());
    }
}
