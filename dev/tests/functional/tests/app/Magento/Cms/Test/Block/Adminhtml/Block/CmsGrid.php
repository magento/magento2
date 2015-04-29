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
            'selector' => '[name="params[filters][title]"]',
        ],
        'identifier' => [
            'selector' => '[name="params[filters][identifier]"]',
        ],
        'is_active' => [
            'selector' => '[name="params[filters][is_active]"]',
            'input' => 'select',
        ],
        'creation_time_from' => [
            'selector' => '[name="params[filters][creation_time][from]"]',
        ],
        'update_time_from' => [
            'selector' => '[name="params[filters][update_time][from]"]',
        ],
        'store_id' => [
            'selector' => '[name="params[filters][store_id]"]',
            'input' => 'selectstore'
        ],
    ];

    /**
     * Locator value for 'Search' button.
     *
     * @var string
     */
    protected $searchButton = '[data-action="grid-filter-apply"]';

    /**
     * Locator value for 'Reset' button.
     *
     * @var string
     */
    protected $resetButton = '[data-action="grid-filter-reset"]';

    /**
     * Locator value for link in action column.
     *
     * @var string
     */
    protected $editLink = 'td[data-part="body.row.cell"]';
}
