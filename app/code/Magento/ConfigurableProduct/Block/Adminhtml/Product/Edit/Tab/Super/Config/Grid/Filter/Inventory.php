<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Configurable product associated products in stock filter
 */
namespace Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Super\Config\Grid\Filter;

use Magento\Backend\Block\Widget\Grid\Column\Filter\Select;

class Inventory extends Select
{
    /**
     * @return array
     */
    protected function _getOptions()
    {
        return [
            ['value' => '', 'label' => ''],
            ['value' => 1, 'label' => __('In Stock')],
            ['value' => 0, 'label' => __('Out of Stock')]
        ];
    }
}
