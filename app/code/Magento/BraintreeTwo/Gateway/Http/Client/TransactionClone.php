<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Gateway\Http\Client;

use Magento\BraintreeTwo\Gateway\Request\CaptureDataBuilder;

/**
 * Class TransactionClone
 */
class TransactionClone extends AbstractTransaction
{
    /**
     * @inheritdoc
     */
    protected function process(array $data)
    {
        $transactionId = $data[CaptureDataBuilder::TRANSACTION_ID];
        unset($data[CaptureDataBuilder::TRANSACTION_ID]);

        return $this->adapter->cloneTransaction($transactionId, $data);
    }
}
