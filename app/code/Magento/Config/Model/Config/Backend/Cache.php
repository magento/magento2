<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Cache cleaner backend model
 *
 */
namespace Magento\Config\Model\Config\Backend;

/**
 * @api
 * @since 2.0.0
 */
class Cache extends \Magento\Framework\App\Config\Value
{
    /**
     * Cache tags to clean
     *
     * @var array
     * @since 2.0.0
     */
    protected $_cacheTags = [];

    /**
     * Clean cache, value was changed
     *
     * @return $this
     * @since 2.0.0
     */
    public function afterSave()
    {
        if ($this->isValueChanged()) {
            $this->_cacheManager->clean($this->_cacheTags);
        }
        return $this;
    }
}
