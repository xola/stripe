<?php

namespace Omnipay\Stripe\Message;

/**
 * Stripe Create Credit Card Request
 */
class CreateCardRequest extends AbstractRequest
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
                // Set email if not already set
                $data['email'] = $this->getCard()->getEmail();
            }
        } else {
            // one of token or card is required
            $this->validate('card');
        }

        return $data;
    }

    public function getEndpoint()
    {
        return $this->endpoint . '/customers';
    }
}
