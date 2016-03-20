<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Model\ResourceModel\PaymentToken;

/**
 * Vault Payment Tokens collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource initialization
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('Magento\Vault\Model\PaymentToken', 'Magento\Vault\Model\ResourceModel\PaymentToken');
    }
}
