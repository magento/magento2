<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
            'selector' => '[name="name"]',
        ],
        'sku' => [
            'selector' => '[name="sku"]',
        ],
        'type' => [
            'selector' => '[name="type_id"]',
            'input' => 'select',
        ],
        'price_from' => [
            'selector' => '[name="price[from]"]',
        ],
        'price_to' => [
            'selector' => '[name="price[to]"]',
        ],
        'qty_from' => [
            'selector' => '[name="qty[from]"]',
        ],
        'qty_to' => [
            'selector' => '[name="qty[to]"]',
        ],
        'visibility' => [
            'selector' => '[name="visibility"]',
            'input' => 'select',
        ],
        'status' => [
            'selector' => '[name="status"]',
            'input' => 'select',
        ],
        'set_name' => [
            'selector' => '[name="attribute_set_id"]',
            'input' => 'select',
        ],
        'store_id' => [
            'selector' => '[name="store_id"]',
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
     * @deprecated for general get attribute method
     * @see getBaseImageAttribute
     */
    public function getBaseImageSource()
    {
        return $this->getBaseImageAttribute('src');
    }

    /**
     * Get attribute from base image component.
     *
     * @param string $attributeName
     * @return string
     */
    public function getBaseImageAttribute($attributeName)
    {
        $baseImage = $this->_rootElement->find($this->baseImage);
        return $baseImage->isVisible() ? $baseImage->getAttribute($attributeName) : '';
    }
}
