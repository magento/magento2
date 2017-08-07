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
 * @since 2.1.0
 */
class BraintreeSearchAdapter
{
    /**
     * @return TextNode
     * @SuppressWarnings(PHPMD.ShortMethodName)
     * @since 2.1.0
     */
    public function id()
    {
        return TransactionSearch::id();
    }

    /**
     * @return MultipleValueNode
     * @since 2.1.0
     */
    public function merchantAccountId()
    {
        return TransactionSearch::merchantAccountId();
    }

    /**
     * @return TextNode
     * @since 2.1.0
     */
    public function orderId()
    {
        return TransactionSearch::orderId();
    }

    /**
     * @return TextNode
     * @since 2.1.0
     */
    public function paypalPaymentId()
    {
        return TransactionSearch::paypalPaymentId();
    }

    /**
     * @return MultipleValueNode
     * @since 2.1.0
     */
    public function createdUsing()
    {
        return TransactionSearch::createdUsing();
    }

    /**
     * @return MultipleValueNode
     * @since 2.1.0
     */
    public function type()
    {
        return TransactionSearch::type();
    }

    /**
     * @return RangeNode
     * @since 2.1.0
     */
    public function createdAt()
    {
        return TransactionSearch::createdAt();
    }

    /**
     * @return RangeNode
     * @since 2.1.0
     */
    public function amount()
    {
        return TransactionSearch::amount();
    }

    /**
     * @return MultipleValueNode
     * @since 2.1.0
     */
    public function status()
    {
        return TransactionSearch::status();
    }

    /**
     * @return TextNode
     * @since 2.1.0
     */
    public function settlementBatchId()
    {
        return TransactionSearch::settlementBatchId();
    }

    /**
     * @return MultipleValueNode
     * @since 2.1.0
     */
    public function paymentInstrumentType()
    {
        return TransactionSearch::paymentInstrumentType();
    }
}
