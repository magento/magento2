<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Ui\Test\Block\Adminhtml;

use Magento\Mtf\Client\Locator;
use Magento\Backend\Test\Block\Widget\Grid;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Backend Data Grid with advanced functionality for managing entities.
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class DataGrid extends Grid
{
    /**
     * Locator value for "Edit" link inside action column.
     *
     * @var string
     */
    protected $editLink = '.action-menu-item[href*="edit"]';

    /**
     * Locator value for container of applied Filters.
     *
     * @var string
     */
    protected $appliedFiltersList = '[data-role="filter-list"]';

    /**
     * Locator value for "Filter" button.
     *
     * @var string
     */
    protected $filterButton = '[data-action="grid-filter-expand"]';

    /**
     * An element locator which allows to select entities in grid.
     *
     * @var string
     */
    protected $selectItem = 'tbody tr [data-action="select-row"]';

    /**
     * Secondary part of row locator template for getRow() method
     *
     * @var string
     */
    protected $rowTemplate = 'td[*[contains(.,normalize-space("%s"))]]';

    /**
     * Secondary part of row locator template for getRow() method with strict option
     *
     * @var string
     */
    protected $rowTemplateStrict = 'td[*[text()[normalize-space()="%s"]]]';

    /**
     * Mass action toggle list.
     *
     * @var string
     */
    protected $massActionToggleList = '//span[contains(@class, "action-menu-item") and .= "%s"]';

    /**
     * Mass action toggle button.
     *
     * @var string
     */
    protected $massActionToggleButton = 'th [data-toggle="dropdown"]';

    /**
     * Mass action button.
     *
     * @var string
     */
    protected $massActionButton = '.action-select';

    /**
     * Locator fo action button.
     *
     * @var string
     */
    protected $actionButton = '.modal-inner-wrap .action-secondary';

    /**
     * Column header locator.
     *
     * @var string
     */
    protected $columnHeader = './/*[@data-role="grid-wrapper"]//th/span[.="%s"]';

    /**
     * @var string
     */
    protected $rowById = "//tr[//input[@data-action='select-row' and @value='%s']]";

    /**
     * @var string
     */
    protected $cellByHeader = "//td[count(//th[span[.='%s']]/preceding-sibling::th)+1]";

    /**
     * @var string
     */
    protected $fullTextSearchField = '.data-grid-search-control-wrap .data-grid-search-control';

    /**
     * @var string
     */
    protected $fullTextSearchButton = '.data-grid-search-control-wrap .action-submit';

    /**
     * Selector for no records row.
     *
     * @var string
     */
    protected $noRecords = '[class$=no-data]';

    /**
     * Selector for alert.
     *
     * @var string
     */
    protected $alertModal = '._show[data-role=modal]';

    /**
     * Locator for 'Sort' link.
     *
     * @var string
     */
    protected $sortLink = "//th[contains(@class, '%s')]/span[contains(text(), '%s')]";

    /**
     * Current page input.
     *
     * @var string
     */
    protected $currentPage = '#pageCurrent';

    /**
     * Clear all applied Filters.
     *
     * @return void
     */
    public function resetFilter()
    {
        $chipsHolder = $this->_rootElement->find($this->appliedFiltersList);
        if ($chipsHolder->isVisible()) {
            parent::resetFilter();
        }
        $this->waitLoader();
    }

    /**
     * Wait filter to load on page.
     *
     * @return void
     */
    protected function waitFilterToLoad()
    {
        $this->getTemplateBlock()->waitForElementNotVisible($this->loader);
        $browser = $this->_rootElement;
        $selector = $this->filterButton . ', ' . $this->resetButton;
        $browser->waitUntil(
            function () use ($browser, $selector) {
                $filter = $browser->find($selector);
                return $filter->isVisible() == true ? true : null;
            }
        );
    }

    /**
     * Open "Filter" block.
     *
     * @return void
     */
    protected function openFilterBlock()
    {
        $this->waitFilterToLoad();

        $toggleFilterButton = $this->_rootElement->find($this->filterButton);
        $searchButton = $this->_rootElement->find($this->searchButton);
        if ($toggleFilterButton->isVisible() && !$searchButton->isVisible()) {
            $toggleFilterButton->click();
            $browser = $this->_rootElement;
            $browser->waitUntil(
                function () use ($searchButton) {
                    return $searchButton->isVisible() ? true : null;
                }
            );
        }
    }

    /**
     * Click on "Edit" link.
     *
     * @param SimpleElement $rowItem
     * @return void
     */
    protected function clickEditLink(SimpleElement $rowItem)
    {
        $rowItem->find($this->editLink)->click();
    }

    /**
     * Search item using Data Grid Filter.
     *
     * @param array $filter
     * @return void
     */
    public function search(array $filter)
    {
        $this->openFilterBlock();
        parent::search($filter);
        $this->waitForElementNotVisible($this->searchButton);
        $this->waitLoader();
    }

    /**
     * Search item and open it.
     *
     * @param array $filter
     * @throws \Exception
     */
    public function searchAndOpen(array $filter)
    {
        $this->search($filter);
        $rowItem = $this->getRow($filter);
        if ($rowItem->isVisible()) {
            $this->clickEditLink($rowItem);
        } else {
            throw new \Exception('Searched item was not found.');
        }
    }

    /**
     * Perform selected massaction over checked items.
     *
     * @param array $items
     * @param array|string $action [array -> key = value from first select; value => value from subselect]
     * @param bool $acceptAlert [optional]
     * @param string $massActionSelection [optional]
     * @return void
     */
    public function massaction(array $items, $action, $acceptAlert = false, $massActionSelection = '')
    {
        $this->waitLoader();
        $this->resetFilter();
        if ($this->_rootElement->find($this->noRecords)->isVisible()) {
            return;
        }
        $this->selectItems($items);
        if ($massActionSelection) {
            $this->_rootElement->find($this->massActionToggleButton)->click();
            $this->_rootElement
                ->find(sprintf($this->massActionToggleList, $massActionSelection), Locator::SELECTOR_XPATH)
                ->click();
        }
        $actionType = is_array($action) ? key($action) : $action;
        $this->_rootElement->find($this->massActionButton)->click();
        $this->_rootElement
            ->find(sprintf($this->massActionToggleList, $actionType), Locator::SELECTOR_XPATH)
            ->click();
        if (is_array($action)) {
            $this->_rootElement
                ->find(sprintf($this->massActionToggleList, end($action)), Locator::SELECTOR_XPATH)
                ->click();
        }
        if ($acceptAlert) {
            $element = $this->browser->find($this->alertModal);
            /** @var \Magento\Ui\Test\Block\Adminhtml\Modal $modal */
            $modal = $this->blockFactory->create('Magento\Ui\Test\Block\Adminhtml\Modal', ['element' => $element]);
            $modal->acceptAlert();
        }
    }

    /**
     * Select items without using grid search.
     *
     * @param array $items
     * @param bool $isSortable
     * @return void
     * @throws \Exception
     */
    public function selectItems(array $items, $isSortable = true)
    {
        if ($isSortable) {
            $this->sortGridByField('ID');
        }
        foreach ($items as $item) {
            $this->_rootElement->find($this->currentPage)->setValue('');
            $this->waitLoader();
            $selectItem = $this->getRow($item)->find($this->selectItem);
            do {
                if ($selectItem->isVisible()) {
                    if (!$selectItem->isSelected()) {
                        $selectItem->click();
                    }
                    break;
                }
            } while ($this->nextPage());
            if (!$selectItem->isVisible()) {
                throw new \Exception('Searched item was not found.');
            }
        }
    }

    /**
     * Sort grid by field.
     *
     * @param string $field
     * @param string $sort
     * @return void
     */
    public function sortGridByField($field, $sort = "desc")
    {
        $reverseSort = $sort == 'desc' ? 'asc' : 'desc';
        $sortBlock = $this->_rootElement->find(sprintf($this->sortLink, $reverseSort, $field), Locator::SELECTOR_XPATH);
        if ($sortBlock->isVisible()) {
            $sortBlock->click();
            $this->waitLoader();
        }
    }

    /**
     * @param string $columnLabel
     */
    public function sortByColumn($columnLabel)
    {
        $this->waitLoader();
        $this->getTemplateBlock()->waitForElementNotVisible($this->loader);
        $this->_rootElement->find(sprintf($this->columnHeader, $columnLabel), Locator::SELECTOR_XPATH)->click();
    }

    /**
     * @return array|string
     */
    public function getFirstItemId()
    {
        $this->waitLoader();
        $this->getTemplateBlock()->waitForElementNotVisible($this->loader);
        return $this->_rootElement->find($this->selectItem)->getValue();
    }

    /**
     * Return ids of all items currently displayed in grid
     *
     * @return string[]
     */
    public function getAllIds()
    {
        $this->waitLoader();
        $this->getTemplateBlock()->waitForElementNotVisible($this->loader);
        $rowsCheckboxes = $this->_rootElement->getElements($this->selectItem);
        $ids = [];
        foreach ($rowsCheckboxes as $checkbox) {
            $ids[] = $checkbox->getValue();
        }
        return $ids;
    }

    /**
     * @param string $id
     * @param string $headerLabel
     * @return array|string
     */
    public function getColumnValue($id, $headerLabel)
    {
        $this->waitLoader();
        $this->getTemplateBlock()->waitForElementNotVisible($this->loader);
        $selector = sprintf($this->rowById, $id) . sprintf($this->cellByHeader, $headerLabel);
        return $this->_rootElement->find($selector, Locator::SELECTOR_XPATH)->getText();
    }

    /**
     * @param string $text
     */
    public function fullTextSearch($text)
    {
        $this->waitLoader();
        $this->getTemplateBlock()->waitForElementNotVisible($this->loader);
        $this->_rootElement->find($this->fullTextSearchField)->setValue($text);
        $this->_rootElement->find($this->fullTextSearchButton)->click();
    }
}
