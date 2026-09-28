<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Integration\Model\ResourceModel\Integration;

/**
 * Integrations collection.
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource collection initialization.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\Integration\Model\Integration::class,
            \Magento\Integration\Model\ResourceModel\Integration::class
        );
    }

    /**
     * Add filter for finding integrations with unsecure URLs.
     *
     * @return $this
     */
    public function addUnsecureUrlsFilter()
    {
        return $this->addFieldToFilter(
            ['endpoint', 'identity_link_url'],
            [['like' => 'http:%'], ['like' => 'http:%']]
        );
    }
}
