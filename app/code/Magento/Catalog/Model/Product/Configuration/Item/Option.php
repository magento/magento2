<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Configuration item option model
 */
namespace Magento\Catalog\Model\Product\Configuration\Item;

class Option extends \Magento\Framework\DataObject implements
    \Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface
{
    /**
     * Returns value of this option
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->_getData('value');
    }
}
