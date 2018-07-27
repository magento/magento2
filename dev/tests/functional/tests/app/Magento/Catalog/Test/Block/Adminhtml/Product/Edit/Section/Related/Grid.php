<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Section\Related;

use Magento\Ui\Test\Block\Adminhtml\DataGrid;

/**
 * Related products grid.
 */
class Grid extends DataGrid
{
    /**
     * Grid fields map
     *
     * @var array
     */
    protected $filters = [
        'name' => [
            'selector' => 'input[name="name"]',
        ],
        'sku' => [
            'selector' => 'input[name="sku"]',
        ],
        'type' => [
            'selector' => 'select[name="type_id"]',
            'input' => 'select',
        ],
    ];
}
