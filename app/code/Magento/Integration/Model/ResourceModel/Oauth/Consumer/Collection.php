<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Integration\Model\ResourceModel\Oauth\Consumer;

/**
 * OAuth Application resource collection model
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialize collection model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\Integration\Model\Oauth\Consumer::class,
            \Magento\Integration\Model\ResourceModel\Oauth\Consumer::class
        );
    }
}
