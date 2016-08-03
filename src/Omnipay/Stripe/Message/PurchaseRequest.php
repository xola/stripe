<?php

namespace Omnipay\Stripe\Message;

/**
 * Stripe Purchase Request
 */
class PurchaseRequest extends AuthorizeRequest
{
    const API_VERSION_STATEMENT_DESCRIPTOR = "2014-12-17";

    public function getData()
    {
        $data = parent::getData();
        $data['capture'] = 'true';

        $apiVersion = $this->getApiVersion();
        if (is_null($apiVersion) || (!is_null($apiVersion) && $apiVersion >= self::API_VERSION_STATEMENT_DESCRIPTOR)) {
            $data['statement_descriptor'] = $this->getStatementDescriptor();
        } else {
            $data['statement_description'] = $this->getStatementDescriptor();
        }

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
