<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Test\Block\Adminhtml\Block;

use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Ui\Test\Block\Adminhtml\DataGrid;

/**
 * Backend Data Grid for managing "SynonymGroup" entities.
 */
class SynonymGroupGrid extends DataGrid
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
        'synonyms' => [
            'selector' => '[name="synonyms"]',
        ],
        'scope_id' => [
            'selector' => '[name="scope_id"]',
            'input' => 'simplifiedselect'
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
