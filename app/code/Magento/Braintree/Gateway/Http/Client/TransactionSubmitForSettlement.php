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
 *
 * @deprecated Starting from Magento 2.3.6 Braintree payment method core integration is deprecated
 * in favor of official payment integration available on the marketplace
 */
class TransactionSubmitForSettlement extends AbstractTransaction
{
    /**
     * @inheritdoc
     */
    protected function process(array $data)
    {
        $storeId = $data['store_id'] ?? null;

        return  $this->adapterFactory->create($storeId)
            ->submitForSettlement($data[CaptureDataBuilder::TRANSACTION_ID], $data[PaymentDataBuilder::AMOUNT]);
    }
}
