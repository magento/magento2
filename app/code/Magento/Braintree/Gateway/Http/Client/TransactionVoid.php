<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Gateway\Http\Client;

/**
 * Class \Magento\Braintree\Gateway\Http\Client\TransactionVoid
 *
 * @since 2.1.0
 */
class TransactionVoid extends AbstractTransaction
{
    /**
     * Process http request
     * @param array $data
     * @return \Braintree\Result\Error|\Braintree\Result\Successful
     * @since 2.1.0
     */
    protected function process(array $data)
    {
        return $this->adapter->void($data['transaction_id']);
    }
}
