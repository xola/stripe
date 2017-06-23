<?php

/**
 * Stripe Abstract Request.
 */
namespace Omnipay\Stripe\Message;
use Guzzle\Common\Event;
use Omnipay\Stripe\Util\StripeQueryAggregator;

/**
 * Stripe Abstract Request.
 *
 * This is the parent class for all Stripe requests.
 *
 * Test modes:
 *
 * Stripe accounts have test-mode API keys as well as live-mode
 * API keys. These keys can be active at the same time. Data
 * created with test-mode credentials will never hit the credit
 * card networks and will never cost anyone money.
 *
 * Unlike some gateways, there is no test mode endpoint separate
 * to the live mode endpoint, the Stripe API endpoint is the same
 * for test and for live.
 *
 * Setting the testMode flag on this gateway has no effect.  To
 * use test mode just use your test mode API key.
 *
 * You can use any of the cards listed at https://stripe.com/docs/testing
 * for testing.
 *
 * @see \Omnipay\Stripe\Gateway
 * @link https://stripe.com/docs/api
 *
 * @method \Omnipay\Stripe\Message\Response send()
 */
abstract class AbstractRequest extends \Omnipay\Common\Message\AbstractRequest
{
    /**
     * Live or Test Endpoint URL.
     *
     * @var string URL
     */
    protected $endpoint = 'https://api.stripe.com/v1';

    /**
     * Get the gateway API Key.
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->getParameter('apiKey');
    }

    /**
     * Set the gateway API Key.
     *
     * @return AbstractRequest provides a fluent interface.
     */
    public function setApiKey($value)
    {
        return $this->setParameter('apiKey', $value);
    }

    public function getApiVersion()
    {
        return $this->getParameter('apiVersion');
    }

    public function setApiVersion($value)
    {
        return $this->setParameter('apiVersion', $value);
    }

    /**
     * @deprecated
     */
    public function getCardToken()
    {
        return $this->getCardReference();
    }

    /**
     * @deprecated
     */
    public function setCardToken($value)
    {
        return $this->setCardReference($value);
    }

    public function setEmail($value)
    {
        return $this->setParameter('email', $value);
    }

    public function getEmail()
    {
        return $this->getParameter('email');
    }

    /**
     * Get the customer reference.
     *
     * @return string
     */
    public function getCustomerReference()
    {
        return $this->getParameter('customerReference');
    }

    /**
     * Set the customer reference.
     *
     * Used when calling CreateCard on an existing customer.  If this
     * parameter is not set then a new customer is created.
     *
     * @return AbstractRequest provides a fluent interface.
     */
    public function setCustomerReference($value)
    {
        return $this->setParameter('customerReference', $value);
    }

    public function getMetadata()
    {
        return $this->getParameter('metadata');
    }

    public function setMetadata($value)
    {
        return $this->setParameter('metadata', $value);
    }

    abstract public function getEndpoint();

    /**
     * Get HTTP Method.
     *
     * This is nearly always POST but can be over-ridden in sub classes.
     *
     * @return string
     */
    public function getHttpMethod()
    {
        return 'POST';
    }

    /**
     * Set the expand params for this request
     *
     * Use this to specify which fields should be returned in expanded form in the response
     *
     * @return AbstractRequest provides a fluent interface.
     */
    public function setExpand($value)
    {
        return $this->setParameter('expand', $value);
    }

    public function getExpand()
    {
        return $this->getParameter('expand');
    }

    public function sendData($data)
    {
        // Stripe only accepts TLS >= v1.2, so make sure Curl is told
        $config = $this->httpClient->getConfig();
        $curlOptions = $config->get('curl.options');
        $curlOptions[CURLOPT_SSLVERSION] = 6;
        $config->set('curl.options', $curlOptions);
        $this->httpClient->setConfig($config);

        // Use custom query aggregator because Stripe only supports a specific format
        $this->httpClient->getEventDispatcher()->addListener('request.before_send', function(Event $event) {
            $request = $event['request'];
            if ($request->getMethod() === 'POST') {
                $request->getQuery()->setAggregator(new StripeQueryAggregator());
            }
        });

        // don't throw exceptions for 4xx errors
        $this->httpClient->getEventDispatcher()->addListener(
            'request.error',
            function ($event) {
                if ($event['response']->isClientError()) {
                    $event->stopPropagation();
                }
            }
        );

        $httpRequest = $this->httpClient->createRequest(
            $this->getHttpMethod(),
            $this->getEndpoint(),
            null,
            $data
        );
        $httpRequest->setHeader('Authorization', 'Basic '.base64_encode($this->getApiKey().':'));
        $apiVersion = $this->getApiVersion();
        if (!empty($apiVersion)) {
            // If user has set an API version use that https://stripe.com/docs/api#versioning
            $httpRequest->setHeader('Stripe-Version', $this->getApiVersion());
        }
        $httpResponse = $httpRequest->send();
        
        $this->response = new Response($this, $httpResponse->json());
        
        if ($httpResponse->hasHeader('Request-Id')) {
            $this->response->setRequestId((string) $httpResponse->getHeader('Request-Id'));
        }

        return $this->response;
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->getParameter('source');
    }

    /**
     * @param $value
     *
     * @return AbstractRequest provides a fluent interface.
     */
    public function setSource($value)
    {
        return $this->setParameter('source', $value);
    }


    /**
     * Get the card data.
     *
     * Because the stripe gateway uses a common format for passing
     * card data to the API, this function can be called to get the
     * data from the associated card object in the format that the
     * API requires.
     *
     * @return array
     */
    protected function getCardData()
    {
        $card = $this->getCard();
        $card->validate();

        $data = array();
        $data['object'] = 'card';
        if ($card->getPostcode()) {
            $data['address_zip'] = $card->getPostcode();
        }
        if ($card->getCvv()) {
            $data['cvc'] = $card->getCvv();
        }
        $tracks = $card->getTracks();
        if (!empty($tracks)) {
            $data['swipe_data'] = $tracks;
            return $data;
        }

        $data['number'] = $card->getNumber();
        $data['exp_month'] = $card->getExpiryMonth();
        $data['exp_year'] = $card->getExpiryYear();

        $data['name'] = $card->getName();
        $data['address_line1'] = $card->getAddress1();
        $data['address_line2'] = $card->getAddress2();
        $data['address_city'] = $card->getCity();
        $data['address_state'] = $card->getState();
        $data['address_country'] = $card->getCountry();
        $data['email']           = $card->getEmail();

        return $data;
    }
}
