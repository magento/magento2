<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Contact\Model\System\Config\Backend;

use Magento\Config\Model\Config\Backend\Cache;

/**
 * Cache cleaner backend model
 * @since 2.0.0
 */
class Links extends Cache implements \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * Cache tags to clean
     *
     * @var string[]
     * @since 2.0.0
     */
    protected $_cacheTags = [\Magento\Store\Model\Store::CACHE_TAG, \Magento\Cms\Model\Block::CACHE_TAG];

    /**
     * Get identities
     *
     * @return array
     * @since 2.0.0
     */
    public function getIdentities()
    {
        return [\Magento\Store\Model\Store::CACHE_TAG, \Magento\Cms\Model\Block::CACHE_TAG];
    }
}
