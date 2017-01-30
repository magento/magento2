<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Test\Block\Adminhtml\Page;

use Magento\Mtf\Client\Locator;
use Magento\Ui\Test\Block\Adminhtml\DataGrid;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Backend Data Grid for managing "CMS Page" entities.
 */
class Grid extends DataGrid
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
        'page_id_from' => [
            'selector' => '[name="page_id[from]"]',
        ],
        'page_id_to' => [
            'selector' => '[name="page_id[to]"]',
        ],
        'title' => [
            'selector' => '[name="title"]',
        ],
        'identifier' => [
            'selector' => '[name="identifier"]',
        ],
        'page_layout' => [
            'selector' => '//label[span[text()="Layout"]]/following-sibling::div',
            'strategy' => 'xpath',
            'input' => 'dropdownmultiselect',
        ],
        'store_id' => [
            'selector' => '[name="store_id"]',
            'input' => 'selectstore'
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
        ]
    ];

    /**
     * Locator value for "Preview" link inside action column.
     *
     * @var string
     */
    protected $previewCmsPage = "..//a[contains(@class, 'action-menu-item') and text() = 'Preview']";

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

    /**
     * Search item and open it on Frontend.
     *
     * @param array $filter
     * @throws \Exception
     * @return void
     */
    public function searchAndPreview(array $filter)
    {
        $this->search($filter);
        $rowItem = $this->getRow([$filter['title']]);
        if ($rowItem->isVisible()) {
            $rowItem->find($this->selectAction)->click();
            $rowItem->find($this->previewCmsPage, Locator::SELECTOR_XPATH)->click();
        } else {
            throw new \Exception('Searched item was not found.');
        }
    }
}
