<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Model\ResourceModel\PaymentToken;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Vault\Model\PaymentToken;
use Magento\Vault\Model\ResourceModel\PaymentToken as ResourcePaymentToken;

/**
 * Vault Payment Tokens collection
 */
class Collection extends AbstractCollection
{
    /**
     * Resource initialization
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(PaymentToken::class, ResourcePaymentToken::class);
    }
}
