<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Gateway\Http\Client;

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
<<<<<<< HEAD

        return $this->adapterFactory->create($storeId)->void($data['transaction_id']);
=======
        // sending store id and other additional keys are restricted by Braintree API
        unset($data['store_id']);

        return $this->adapterFactory->create($storeId)
            ->void($data['transaction_id']);
>>>>>>> upstream/2.2-develop
    }
}
