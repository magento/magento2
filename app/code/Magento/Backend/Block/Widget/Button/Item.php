<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Block\Widget\Button;

/**
 * @api
 * @method string getButtonKey()
 * @method string getRegion()
 * @method string getName()
 * @method int getLevel()
 * @method int getSortOrder()
 * @method string getTitle()
 * @since 2.0.0
 */
class Item extends \Magento\Framework\DataObject
{
    /**
     * Object delete flag
     *
     * @var bool
     * @since 2.0.0
     */
    protected $_isDeleted = false;

    /**
     * Set _isDeleted flag value (if $isDeleted parameter is defined) and return current flag value
     *
     * @param boolean $isDeleted
     * @return bool
     * @since 2.0.0
     */
    public function isDeleted($isDeleted = null)
    {
        $result = $this->_isDeleted;
        if ($isDeleted !== null) {
            $this->_isDeleted = $isDeleted;
        }
        return $result;
    }
}
