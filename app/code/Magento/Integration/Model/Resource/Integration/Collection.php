<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Model\Resource\Integration;

/**
 * Integrations collection.
 */
class Collection extends \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * Resource collection initialization.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Integration\Model\Integration', 'Magento\Integration\Model\Resource\Integration');
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
