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
        $data['statement_descriptor'] = $this->getStatementDescriptor();
        return $data;
    }

    public function setStatementDescriptor($value)
    {
        return $this->setParameter('statementDescriptor', $value);
    }

    public function getStatementDescriptor()
    {
        $statementDesc = $this->getParameter('statementDescriptor');
        // Stripe allows a statement descriptor upto 15 char. Trim  it.
        $statementDesc = substr($statementDesc, 0, 15);
        return $statementDesc;
    }
}
