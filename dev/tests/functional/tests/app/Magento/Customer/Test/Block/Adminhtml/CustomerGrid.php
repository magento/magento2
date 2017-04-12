<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Block\Adminhtml;

use Magento\Ui\Test\Block\Adminhtml\DataGrid;

/**
 * Class CustomerGrid
 * Backend customer grid
 *
 */
class CustomerGrid extends DataGrid
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
        'entity_id_from' => [
            'selector' => '[name="entity_id[from]"]',
        ],
        'entity_id_to' => [
            'selector' => '[name="entity_id[to]"]',
        ],
    ];
}
