<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Test\Block\Adminhtml\Block;

use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Ui\Test\Block\Adminhtml\DataGrid;

/**
 * Backend Data Grid for managing "CMS Block" entities.
 */
class CmsGrid extends DataGrid
{
    /**
     * Select action toggle.
     *
     * @var string
     */
    protected $selectAction = '.action-select';

    /**
     * Filters array mapping.
     *
     * @var array
     */
    protected $filters = [
        'block_id_from' => [
            'selector' => '[name="filters[block_id][from]"]',
        ],
        'block_id_to' => [
            'selector' => '[name="filters[block_id][to]"]',
        ],
        'title' => [
            'selector' => '[name="filters[title]"]',
        ],
        'identifier' => [
            'selector' => '[name="filters[identifier]"]',
        ],
        'store_id' => [
            'selector' => '[name="filters[store_id]"]',
            'input' => 'simplifiedselect'
        ],
        'is_active' => [
            'selector' => '[name="filters[is_active]"]',
            'input' => 'select',
        ],
        'creation_time_from' => [
            'selector' => '[name="filters[creation_time][from]"]',
        ],
        'creation_time_to' => [
            'selector' => '[name="filters[creation_time][to]"]',
        ],
        'update_time_from' => [
            'selector' => '[name="filters[update_time][from]"]',
        ],
        'update_time_to' => [
            'selector' => '[name="filters[update_time][to]"]',
        ],
    ];

    /**
     * Click on "Edit" link.
     *
     * @param SimpleElement $rowItem
     * @return void
     */
    protected function clickEditLink(SimpleElement $rowItem)
    {
        $rowItem->find($this->selectAction)->click();
        $rowItem->find($this->editLink)->click();
    }
}
