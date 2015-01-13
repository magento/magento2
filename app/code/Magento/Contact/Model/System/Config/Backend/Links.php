<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Contact\Model\System\Config\Backend;

/**
 * Cache cleaner backend model
 */
class Links extends \Magento\Backend\Model\Config\Backend\Cache implements \Magento\Framework\Object\IdentityInterface
{
    /**
     * Cache tags to clean
     *
     * @var string[]
     */
    protected $_cacheTags = [\Magento\Store\Model\Store::CACHE_TAG, \Magento\Cms\Model\Block::CACHE_TAG];

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [\Magento\Store\Model\Store::CACHE_TAG, \Magento\Cms\Model\Block::CACHE_TAG];
    }
}
