<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Configuration item option model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Model\Product\Configuration\Item;

class Option extends \Magento\Framework\Object implements
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
