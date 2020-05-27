<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Gateway\Http\Client;

/**
 * Class TransactionVoid
 *
 * @deprecated Starting from Magento 2.3.6 Braintree payment method core integration is deprecated
 * in favor of official payment integration available on the marketplace
 */
class TransactionVoid extends AbstractTransaction
{
    /**
     * Process http request
     * @param array $data
     * @return \Braintree\Result\Error|\Braintree\Result\Successful
     */
    protected function process(array $data)
    {
        $storeId = $data['store_id'] ?? null;

        return $this->adapterFactory->create($storeId)->void($data['transaction_id']);
    }
}
