<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Test\Block\Adminhtml\Product;

use Magento\Backend\Test\Block\Widget\Grid as ParentGrid;

/**
 * Class Grid
 * Review catalog product grid
 */
class Grid extends ParentGrid
{
    /**
     * Grid filter selectors
     *
     * @var array
     */
    protected $filters = [
        'id' => [
            'selector' => 'input[name="entity_id"]',
        ],
        'name' => [
            'selector' => 'input[name="name"]',
        ],
    ];

    /**
     * Locator value for link in action column
     *
     * @var string
     */
    protected $editLink = '.col-entity_id';
}
