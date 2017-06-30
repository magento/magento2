<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
            'selector' => '[name="block_id[from]"]',
        ],
        'block_id_to' => [
            'selector' => '[name="block_id[to]"]',
        ],
        'title' => [
            'selector' => '[name="title"]',
        ],
        'identifier' => [
            'selector' => '[name="identifier"]',
        ],
        'store_id' => [
            'selector' => '[name="store_id"]',
            'input' => 'simplifiedselect'
        ],
        'is_active' => [
            'selector' => '//label[span[text()="Status"]]/following-sibling::div',
            'strategy' => 'xpath',
            'input' => 'dropdownmultiselect',
        ],
        'creation_time_from' => [
            'selector' => '[name="creation_time[from]"]',
        ],
        'creation_time_to' => [
            'selector' => '[name="creation_time[to]"]',
        ],
        'update_time_from' => [
            'selector' => '[name="update_time[from]"]',
        ],
        'update_time_to' => [
            'selector' => '[name="update_time[to]"]',
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
