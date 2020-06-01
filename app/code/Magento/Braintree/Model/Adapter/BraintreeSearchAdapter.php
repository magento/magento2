<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model\Adapter;

use Braintree\MultipleValueNode;
use Braintree\RangeNode;
use Braintree\TextNode;
use Braintree\Transaction;
use Braintree\TransactionSearch;

/**
 * Class Braintree Search Adapter
 * @codeCoverageIgnore
 * @deprecated Starting from Magento 2.3.6 Braintree payment method core integration is deprecated
 * in favor of official payment integration available on the marketplace
 */
class BraintreeSearchAdapter
{
    /**
     * @return TextNode
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function id()
    {
        return TransactionSearch::id();
    }

    /**
     * @return MultipleValueNode
     */
    public function merchantAccountId()
    {
        return TransactionSearch::merchantAccountId();
    }

    /**
     * @return TextNode
     */
    public function orderId()
    {
        return TransactionSearch::orderId();
    }

    /**
     * @return TextNode
     */
    public function paypalPaymentId()
    {
        return TransactionSearch::paypalPaymentId();
    }

    /**
     * @return MultipleValueNode
     */
    public function createdUsing()
    {
        return TransactionSearch::createdUsing();
    }

    /**
     * @return MultipleValueNode
     */
    public function type()
    {
        return TransactionSearch::type();
    }

    /**
     * @return RangeNode
     */
    public function createdAt()
    {
        return TransactionSearch::createdAt();
    }

    /**
     * @return RangeNode
     */
    public function amount()
    {
        return TransactionSearch::amount();
    }

    /**
     * @return MultipleValueNode
     */
    public function status()
    {
        return TransactionSearch::status();
    }

    /**
     * @return TextNode
     */
    public function settlementBatchId()
    {
        return TransactionSearch::settlementBatchId();
    }

    /**
     * @return MultipleValueNode
     */
    public function paymentInstrumentType()
    {
        return TransactionSearch::paymentInstrumentType();
    }
}
