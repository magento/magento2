<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Widget;

use Magento\Backend\Test\Block\Widget\Grid;

/**
 * Class Chooser
 * Backend Cms Page select product grid
 */
class Chooser extends Grid
{
    /**
     * Filters array mapping
     *
     * @var array
     */
    protected $filters = [
        'chooser_sku' => [
            'selector' => 'input[name="chooser_sku"]',
        ],
    ];

    /**
     * Locator value for link in sku column
     *
     * @var string
     */
    protected $editLink = 'td.col-sku';
}
