<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Attribute\Set;

use Magento\Backend\Test\Block\Widget\Grid as AbstractGrid;

/**
 * Class Grid
 * Attribute Set grid
 */
class Grid extends AbstractGrid
{
    /**
     * Locator value for link in action column
     *
     * @var string
     */
    protected $editLink = 'td[data-column="set_name"]';

    /**
     * Filters array mapping
     *
     * @var array
     */
    protected $filters = [
        'set_name' => [
            'selector' => 'input[name="set_name"]',
        ],
    ];
}
