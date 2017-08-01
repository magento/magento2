<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Gateway\Http\Client;

use Magento\Braintree\Gateway\Request\CaptureDataBuilder;
use Magento\Braintree\Gateway\Request\PaymentDataBuilder;

/**
 * Class TransactionSubmitForSettlement
 * @since 2.1.0
 */
class TransactionSubmitForSettlement extends AbstractTransaction
{
    /**
     * @inheritdoc
     * @since 2.1.0
     */
    protected function process(array $data)
    {
        return  $this->adapter->submitForSettlement(
            $data[CaptureDataBuilder::TRANSACTION_ID],
            $data[PaymentDataBuilder::AMOUNT]
        );
    }
}
