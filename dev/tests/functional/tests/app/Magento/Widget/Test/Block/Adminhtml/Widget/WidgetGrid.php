<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\Block\Adminhtml\Widget;

use Magento\Backend\Test\Block\Widget\Grid as AbstractGrid;

/**
 * Widget grid on the Widget Instance Index page.
 */
class WidgetGrid extends AbstractGrid
{
    /**
     * Locator value for link in action column.
     *
     * @var string
     */
    protected $editLink = 'tbody tr td.col-title';

    /**
     * First row selector.
     *
     * @var string
     */
    protected $firstRowSelector = '//tbody//tr[@data-role="row"]/td[contains(@class, "col-title")][1]';

    /**
     * Filters array mapping.
     *
     * @var array
     */
    protected $filters = [
        'title' => [
            'selector' => 'input[name="title"]',
        ],
    ];
}
