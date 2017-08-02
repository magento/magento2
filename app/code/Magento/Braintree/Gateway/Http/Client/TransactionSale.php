<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Gateway\Http\Client;

/**
 * Class TransactionSale
 * @since 2.1.0
 */
class TransactionSale extends AbstractTransaction
{
    /**
     * @inheritdoc
     * @since 2.1.0
     */
    protected function process(array $data)
    {
        return $this->adapter->sale($data);
    }
}
