<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Model\ResourceModel\Integration;

/**
 * Integrations collection.
 * @since 2.0.0
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource collection initialization.
     *
     * @return void
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function addUnsecureUrlsFilter()
    {
        return $this->addFieldToFilter(
            ['endpoint', 'identity_link_url'],
            [['like' => 'http:%'], ['like' => 'http:%']]
        );
    }
}
