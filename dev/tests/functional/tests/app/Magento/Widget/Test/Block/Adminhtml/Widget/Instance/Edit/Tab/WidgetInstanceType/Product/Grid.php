<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\Block\Adminhtml\Widget\Instance\Edit\Tab\WidgetInstanceType\Product;

/**
 * Chooser product grid.
 */
class Grid extends \Magento\Backend\Test\Block\Widget\Grid
{
    /**
     * An element locator which allows to select entities in grid.
     *
     * @var string
     */
    protected $selectItem = 'tbody tr td.a-center.col-in_products';

    /**
     * Filters array mapping.
     *
     * @var array
     */
    protected $filters = [
        'name' => [
            'selector' => 'input[name="chooser_name"]',
        ],
        'sku' => [
            'selector' => 'input[name="chooser_sku"]',
        ],
    ];
}
