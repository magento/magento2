<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Block\Adminhtml;

use Magento\Backend\Test\Block\Widget\Grid as AbstractGrid;

/**
 * Class CustomerGrid
 * Backend customer grid
 *
 */
class CustomerGrid extends AbstractGrid
{
    /**
     * Selector for action option select
     *
     * @var string
     */
    protected $option = '[name="group"]';

    /**
     * Filters array mapping
     *
     * @var array
     */
    protected $filters = [
        'name' => [
            'selector' => '.admin__data-grid-filters input[name*=name]',
        ],
        'email' => [
            'selector' => '.admin__data-grid-filters input[name*=email]',
        ],
        'group' => [
            'selector' => '.admin__data-grid-filters select[name*=group_id]',
            'input' => 'select',
        ],
    ];
}
