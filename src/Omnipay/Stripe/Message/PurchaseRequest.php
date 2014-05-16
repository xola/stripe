<?php

namespace Omnipay\Stripe\Message;

/**
 * Stripe Purchase Request
 */
class PurchaseRequest extends AuthorizeRequest
{
    public function getData()
    {
        $data = parent::getData();
        $data['capture'] = 'true';
        $data['statement_description'] = $this->getStatementDescriptor();
        return $data;
    }

    public function setStatementDescriptor($value)
    {
        return $this->setParameter('statementDescriptor', $value);
    }

    public function getStatementDescriptor()
    {
        return $this->getParameter('statementDescriptor');
    }
}
