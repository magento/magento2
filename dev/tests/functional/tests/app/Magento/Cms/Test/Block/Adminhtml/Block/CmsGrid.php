<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Test\Block\Adminhtml\Block;

use Magento\Backend\Test\Block\Widget\Grid;

/**
 * Adminhtml Cms Block management grid.
 */
class CmsGrid extends Grid
{
    /**
     * Filters array mapping.
     *
     * @var array
     */
    protected $filters = [
        'title' => [
            'selector' => '#title',
        ],
        'identifier' => [
            'selector' => '#identifier',
        ],
        'is_active' => [
            'selector' => '#is_active',
            'input' => 'select',
        ],
        'creation_time_from' => [
            'selector' => '(//span[.="Created"]/following::input[contains(@placeholder,"From")])[1]',
            'strategy' => 'xpath',
        ],
        'update_time_from' => [
            'selector' => '(//span[.="Created"]/following::input[contains(@placeholder,"From")])[2]',
            'strategy' => 'xpath',
        ],
        'store_id' => [
            'selector' => 'label[for="store_id"] + div > select',
            'input' => 'selectstore'
        ],
    ];

    /**
     * Locator value for 'Search' button.
     *
     * @var string
     */
    protected $searchButton = '.action-apply';

    /**
     * Locator value for 'Reset' button.
     *
     * @var string
     */
    protected $resetButton = '.action-reset';

    /**
     * Locator value for link in action column.
     *
     * @var string
     */
    protected $editLink = 'td[data-part="body.row.cell"]';
}
