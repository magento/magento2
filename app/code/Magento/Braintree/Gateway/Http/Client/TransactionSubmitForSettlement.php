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
 */
class TransactionSubmitForSettlement extends AbstractTransaction
{
    /**
     * @inheritdoc
     */
    protected function process(array $data)
    {
        $storeId = !empty($data['store_id']) ? $data['store_id'] : null;
        // sending store id and other additional keys are restricted by Braintree API
        unset($data['store_id']);

        return  $this->adapterFactory->create($storeId)
            ->submitForSettlement($data[CaptureDataBuilder::TRANSACTION_ID], $data[PaymentDataBuilder::AMOUNT]);
    }
}
