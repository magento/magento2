<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Configuration item option model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
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
