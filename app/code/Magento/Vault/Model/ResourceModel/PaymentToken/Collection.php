<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Model\ResourceModel\PaymentToken;

/**
 * Vault Payment Tokens collection
 * @since 2.1.0
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource initialization
     *
     * @return void
     * @since 2.1.0
     */
    public function _construct()
    {
        $this->_init(\Magento\Vault\Model\PaymentToken::class, \Magento\Vault\Model\ResourceModel\PaymentToken::class);
    }
}
