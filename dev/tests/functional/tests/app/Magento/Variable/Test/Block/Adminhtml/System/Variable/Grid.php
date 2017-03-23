<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Variable\Test\Block\Adminhtml\System\Variable;

use Magento\Backend\Test\Block\Widget\Grid as AbstractGrid;

/**
 * System Variable management grid.
 */
class Grid extends AbstractGrid
{
    /**
     * Locator value for link in action column.
     *
     * @var string
     */
    protected $editLink = 'td[class*=col-code]';

    /**
     * Initialize block elements.
     *
     * @var array
     */
    protected $filters = [
        'code' => [
            'selector' => 'input[name="code"]',
        ],
        'name' => [
            'selector' => 'input[name="name"]',
        ],
    ];
}
