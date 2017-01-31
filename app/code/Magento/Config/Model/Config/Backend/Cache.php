<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Cache cleaner backend model
 *
 */
namespace Magento\Config\Model\Config\Backend;

class Cache extends \Magento\Framework\App\Config\Value
{
    /**
     * Cache tags to clean
     *
     * @var array
     */
    protected $_cacheTags = [];

    /**
     * Clean cache, value was changed
     *
     * @return $this
     */
    public function afterSave()
    {
        if ($this->isValueChanged()) {
            $this->_cacheManager->clean($this->_cacheTags);
        }
        return $this;
    }
}
