<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Configuration item option model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Model\Product\Configuration\Item;

/**
 * Class \Magento\Catalog\Model\Product\Configuration\Item\Option
 *
 * @since 2.0.0
 */
class Option extends \Magento\Framework\DataObject implements
    \Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface
{
    /**
     * Returns value of this option
     *
     * @return mixed
     * @since 2.0.0
     */
    public function getValue()
    {
        return $this->_getData('value');
    }
}
