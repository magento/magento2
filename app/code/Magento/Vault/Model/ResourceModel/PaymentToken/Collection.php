<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
        $this->_init(\Magento\Vault\Model\PaymentToken::class, \Magento\Vault\Model\ResourceModel\PaymentToken::class);
    }
}
