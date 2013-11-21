<?php

namespace Omnipay\Stripe\Message;

/**
 * Stripe Update Credit Card Request
 */
class UpdateCardRequest extends AbstractRequest
{
    public function getData()
    {
        $data = array();
        $data['description'] = $this->getDescription();
        $data['email'] = $this->getEmail();

        if ($this->getToken()) {
            $data['card'] = $this->getToken();
        } elseif ($this->getCard()) {
            $data['card'] = $this->getCardData();
            if (!$data['email']) {
                // Set email in case not already set
                $data['email'] = $this->getCard()->getEmail();
            }
        }

        $this->validate('cardReference');

        return $data;
    }

    public function getEndpoint()
    {
        return $this->endpoint . '/customers/' . $this->getCardReference();
    }
}
