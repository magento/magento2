<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Backend\Test\Block\System\Variable;

use Magento\Backend\Test\Block\Widget\Grid as AbstractGrid;

/**
 * Class Grid
 * System Variable management grid
 */
class Grid extends AbstractGrid
{
    /**
     * Locator value for link in action column
     *
     * @var string
     */
    protected $editLink = 'td[class*=col-code]';

    /**
     * Initialize block elements
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
