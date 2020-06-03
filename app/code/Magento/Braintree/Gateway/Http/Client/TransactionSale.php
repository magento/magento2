<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Gateway\Http\Client;

/**
 * Class TransactionSale
 *
 * @deprecated Starting from Magento 2.3.6 Braintree payment method core integration is deprecated
 * in favor of official payment integration available on the marketplace
 */
class TransactionSale extends AbstractTransaction
{
    /**
     * @inheritdoc
     */
    protected function process(array $data)
    {
        $storeId = $data['store_id'] ?? null;
        // sending store id and other additional keys are restricted by Braintree API
        unset($data['store_id']);

        return $this->adapterFactory->create($storeId)->sale($data);
    }
}
