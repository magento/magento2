<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product;

use Magento\Ui\Test\Block\Adminhtml\DataGrid;

/**
 * Backend catalog product grid.
 */
class Grid extends DataGrid
{
    /**
     * Row pattern.
     *
     * @var string
     */
    protected $rowPattern = './/tr[%s]';

    /**
     * Filters array mapping.
     *
     * @var array
     */
    protected $filters = [
        'name' => [
            'selector' => '[name="filters[name]"]',
        ],
        'sku' => [
            'selector' => '[name="filters[sku]"]',
        ],
        'type' => [
            'selector' => '[name="filters[type_id]"]',
            'input' => 'select',
        ],
        'price_from' => [
            'selector' => '[name="filters[price][from]"]',
        ],
        'price_to' => [
            'selector' => '[name="filters[price][to]"]',
        ],
        'qty_from' => [
            'selector' => '[name="filters[qty][from]"]',
        ],
        'qty_to' => [
            'selector' => '[name="filters[qty][to]"]',
        ],
        'visibility' => [
            'selector' => '[name="filters[visibility]"]',
            'input' => 'select',
        ],
        'status' => [
            'selector' => '[name="filters[status]"]',
            'input' => 'select',
        ],
        'set_name' => [
            'selector' => '[name="filters[attribute_set_id]"]',
            'input' => 'select',
        ],
    ];

    /**
     * Product base image.
     *
     * @var string
     */
    protected $baseImage = '.data-grid-thumbnail-cell img';

    /**
     * Update attributes for selected items.
     *
     * @param array $items [optional]
     * @return void
     */
    public function updateAttributes(array $items = [])
    {
        $this->massaction($items, 'Update attributes');
    }

    /**
     * Get base image source link.
     *
     * @return string
     */
    public function getBaseImageSource()
    {
        $baseImage = $this->_rootElement->find($this->baseImage);
        return $baseImage->isVisible() ? $baseImage->getAttribute('src') : '';
    }
}
