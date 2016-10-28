<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Attribute;

use Magento\Backend\Test\Block\Widget\Grid as AbstractGrid;

/**
 * Class Grid
 * Attribute grid of Product Attributes
 */
class Grid extends AbstractGrid
{
    /**
     * Locator value for link in action column.
     *
     * @var string
     */
    protected $editLink = 'td.col-frontend_label';

    /**
     * Filters array mapping.
     *
     * @var array
     */
    protected $filters = [
        'attribute_code' => [
            'selector' => 'input[name="attribute_code"]',
        ],
        'frontend_label' => [
            'selector' => 'input[name="frontend_label"]',
        ],
        'is_required' => [
            'selector' => 'select[name="is_required"]',
            'input' => 'select',
        ],
        'is_user_defined' => [
            'selector' => 'select[name="is_user_defined"]',
            'input' => 'select',
        ],
        'is_visible' => [
            'selector' => 'select[name="is_visible"]',
            'input' => 'select',
        ],
        'is_global' => [
            'selector' => 'select[name="is_global"]',
            'input' => 'select',
        ],
        'is_searchable' => [
            'selector' => 'select[name="is_searchable"]',
            'input' => 'select',
        ],
        'is_filterable' => [
            'selector' => 'select[name="is_filterable"]',
            'input' => 'select',
        ],
        'is_comparable' => [
            'selector' => 'select[name="is_comparable"]',
            'input' => 'select',
        ],
    ];
}
