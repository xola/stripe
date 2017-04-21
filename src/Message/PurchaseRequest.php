<?php

/**
 * Stripe Purchase Request.
 */
namespace Omnipay\Stripe\Message;

/**
 * Stripe Purchase Request.
 *
 * To charge a credit card, you create a new charge object. If your API key
 * is in test mode, the supplied card won't actually be charged, though
 * everything else will occur as if in live mode. (Stripe assumes that the
 * charge would have completed successfully).
 *
 * Example:
 *
 * <code>
 *   // Create a gateway for the Stripe Gateway
 *   // (routes to GatewayFactory::create)
 *   $gateway = Omnipay::create('Stripe');
 *
 *   // Initialise the gateway
 *   $gateway->initialize(array(
 *       'apiKey' => 'MyApiKey',
 *   ));
 *
 *   // Create a credit card object
 *   // This card can be used for testing.
 *   $card = new CreditCard(array(
 *               'firstName'    => 'Example',
 *               'lastName'     => 'Customer',
 *               'number'       => '4242424242424242',
 *               'expiryMonth'  => '01',
 *               'expiryYear'   => '2020',
 *               'cvv'          => '123',
 *               'email'                 => 'customer@example.com',
 *               'billingAddress1'       => '1 Scrubby Creek Road',
 *               'billingCountry'        => 'AU',
 *               'billingCity'           => 'Scrubby Creek',
 *               'billingPostcode'       => '4999',
 *               'billingState'          => 'QLD',
 *   ));
 *
 *   // Do a purchase transaction on the gateway
 *   $transaction = $gateway->purchase(array(
 *       'amount'                   => '10.00',
 *       'currency'                 => 'USD',
 *       'description'              => 'This is a test purchase transaction.',
 *       'card'                     => $card,
 *   ));
 *   $response = $transaction->send();
 *   if ($response->isSuccessful()) {
 *       echo "Purchase transaction was successful!\n";
 *       $sale_id = $response->getTransactionReference();
 *       echo "Transaction reference = " . $sale_id . "\n";
 *   }
 * </code>
 *
 * Because a purchase request in Stripe looks similar to an
 * Authorize request, this class simply extends the AuthorizeRequest
 * class and over-rides the getData method setting capture = true.
 *
 * @see \Omnipay\Stripe\Gateway
 * @link https://stripe.com/docs/api#charges
 */
class PurchaseRequest extends AuthorizeRequest
{
    const API_VERSION_STATEMENT_DESCRIPTOR = "2014-12-17";

    public function getData()
    {
        $data = parent::getData();
        $data['capture'] = 'true';

        if ($items = $this->getItems())
        {
            $lineItems = [];
            foreach ($items as $item) {
                $lineItem = [];
                $lineItem['product_code'] = $item->getName();
                $lineItem['product_description'] = $item->getDescription();
                $lineItem['unit_cost'] = $this->getAmountWithCurrencyPrecision($item->getPrice());
                $lineItem['quantity'] = $item->getQuantity();
                $lineItems[] = $lineItem;
            }
            $data['level3'] = [
                'merchant_reference' => $this->getTransactionId(),
                'line_items' => $lineItems
            ];
        }

        return $data;
    }

    private function getAmountWithCurrencyPrecision($amount)
    {
        return (int)round($amount * pow(10, $this->getCurrencyDecimalPlaces()));
    }
}
