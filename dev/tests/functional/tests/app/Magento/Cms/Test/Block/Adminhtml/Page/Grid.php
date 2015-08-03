<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Test\Block\Adminhtml\Page;

use Magento\Mtf\Client\Locator;
use Magento\Ui\Test\Block\Adminhtml\DataGrid;

/**
 * Backend Data Grid for managing "CMS Page" entities.
 */
class Grid extends DataGrid
{
    /**
     * Filters array mapping.
     *
     * @var array
     */
    protected $filters = [
        'page_id_from' => [
            'selector' => '[name="filters[page_id][from]"]',
        ],
        'page_id_to' => [
            'selector' => '[name="filters[page_id][to]"]',
        ],
        'title' => [
            'selector' => '[name="filters[title]"]',
        ],
        'identifier' => [
            'selector' => '[name="filters[identifier]"]',
        ],
        'page_layout' => [
            'selector' => '[name="filters[page_layout]"]',
            'input' => 'select',
        ],
        'store_id' => [
            'selector' => '[name="filters[store_id]"]',
            'input' => 'selectstore'
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
        'under_version_control' => [
            'selector' => '[name="filters[under_version_control]"]',
        ],
    ];

    /**
     * Locator value for "Preview" link inside action column.
     *
     * @var string
     */
    protected $previewCmsPage = "..//a[contains(@class, 'action-menu-item') and text() = '%s']";
    
    /**
     * Search item and open it on Frontend.
     *
     * @param array $filter
     * @throws \Exception
     * @return void
     */
    public function searchAndPreview(array $filter)
    {
        $itemName = 'Preview';
        $this->search($filter);
        $rowItem = $this->_rootElement->find($this->rowItem);
        if ($rowItem->isVisible()) {
            $selector = sprintf('//tr[td="%s"]//*[@class="action-select"]', $filter['title']);
            $selectedRow = $this->_rootElement->find($selector, Locator::SELECTOR_XPATH);
            $selectedRow->click();
            $selectedRow->find(sprintf($this->previewCmsPage, $itemName), Locator::SELECTOR_XPATH)->click();
        } else {
            throw new \Exception('Searched item was not found.');
        }
    }

    /**
     * Wait loader.
     *
     * @return void
     */
    protected function waitLoader()
    {
        try {
            $browser = $this->browser;
            $selector = $this->loader;
            $browser->waitUntil(
                function () use ($browser, $selector) {
                    return $browser->find($selector)->isVisible() == true ? true : null;
                }
            );
        } catch (\Exception $e) {
        }

        parent::waitLoader();
    }
}
