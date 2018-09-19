<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Gateway\Http\Client;

use Magento\Braintree\Gateway\Request\PaymentDataBuilder;

class TransactionRefund extends AbstractTransaction
{
    /**
     * Process http request
     * @param array $data
     * @return \Braintree\Result\Error|\Braintree\Result\Successful
     */
    protected function process(array $data)
    {
        $storeId = $data['store_id'] ?? null;

        return $this->adapterFactory->create($storeId)
            ->refund($data['transaction_id'], $data[PaymentDataBuilder::AMOUNT]);
    }
}
