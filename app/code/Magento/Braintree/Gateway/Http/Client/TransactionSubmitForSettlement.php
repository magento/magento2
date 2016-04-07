<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Gateway\Http\Client;

use Magento\Braintree\Gateway\Request\CaptureDataBuilder;
use Magento\Braintree\Gateway\Request\PaymentDataBuilder;

/**
 * Class TransactionSubmitForSettlement
 */
class TransactionSubmitForSettlement extends AbstractTransaction
{
    /**
     * @inheritdoc
     */
    protected function process(array $data)
    {
        return  $this->adapter->submitForSettlement(
            $data[CaptureDataBuilder::TRANSACTION_ID],
            $data[PaymentDataBuilder::AMOUNT]
        );
    }
}
