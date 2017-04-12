<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * Mass action toggle button (located in the Grid).
     *
     * @var string
     */
    protected $massActionToggleButton = '//th//button[@data-toggle="dropdown"]';

    /**
     * Mass action toggle list.
     *
     * @var string
     */
    protected $massActionToggleList = './/span[contains(@class, "action-menu-item") and .= "%s"]';

    /**
     * Action button (located above the Grid).
     *
     * @var string
     */
    protected $actionButton = '.action-select';

    /**
     * Action list.
     *
     * @var string
     */
    protected $actionList = './/span[contains(@class, "action-menu-item") and .= "%s"]';

    /**
     * Column header locator.
     *
     * @var string
     */
    protected $columnHeader = './/*[@data-role="grid-wrapper"]//th/span[.="%s"]';

    /**
     * Grid row xpath locator.
     *
     * @var string
     */
    protected $rowById = ".//tr[td//input[@data-action='select-row' and @value='%s']]";

    /**
     * Column header number.
     *
     * @var string
     */
    protected $columnNumber = ".//th[span[.='%s']][not(ancestor::*[@class='sticky-header'])]/preceding-sibling::th";

    /**
     * Cell number.
     *
     * @var string
     */
    protected $cellByHeader = "//td[%s+1]";

    // @codingStandardsIgnoreStart
    /**
     * Admin data grid header selector.
     *
     * @var string
     */
    private $gridHeader = './/div[@class="admin__data-grid-header"][(not(ancestor::*[@class="sticky-header"]) and not(contains(@style,"visibility: hidden"))) or (ancestor::*[@class="sticky-header" and not(contains(@style,"display: none"))])]';
    // @codingStandardsIgnoreEnd

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
    protected $sortLink = './/div[@data-role="grid-wrapper"]//th[contains(@class, "%s")]/span[contains(text(), "%s")]';

    /**
     * Current page input.
     *
     * @var string
     */
    protected $currentPage = ".//*[@data-ui-id='current-page-input'][not(ancestor::*[@class='sticky-header'])]";

    /**
     * Clear all applied Filters.
     *
     * @return void
     */
    public function resetFilter()
    {
        $chipsHolder = $this->getGridHeaderElement()->find($this->appliedFiltersList);
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
        $this->getTemplateBlock()->waitLoader();
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
            throw new \Exception("Searched item was not found by filter\n" . print_r($filter, true));
        }
        $this->waitLoader();
    }

    /**
     * Search item and select it.
     *
     * @param array $filter
     * @throws \Exception
     */
    public function searchAndSelect(array $filter)
    {
        $this->search($filter);
        $rowItem = $this->getRow($filter);
        if ($rowItem->isVisible()) {
            $rowItem->find($this->selectItem)->click();
        } else {
            throw new \Exception("Searched item was not found by filter\n" . print_r($filter, true));
        }
        $this->waitLoader();
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
            $this->selectMassAction($massActionSelection);
        }
        $this->selectAction($action);
        if ($acceptAlert) {
            $element = $this->browser->find($this->alertModal);
            /** @var \Magento\Ui\Test\Block\Adminhtml\Modal $modal */
            $modal = $this->blockFactory->create(
                \Magento\Ui\Test\Block\Adminhtml\Modal::class,
                ['element' => $element]
            );
            $modal->acceptAlert();
        }
    }

    /**
     * Do mass select/deselect using the dropdown in the grid.
     *
     * @param string $massActionSelection
     * @return void
     */
    public function selectMassAction($massActionSelection)
    {
        //Checks which dropdown is visible and uses it.
        for ($i = 1; $i <= 2; $i++) {
            $massActionButton = '(' . $this->massActionToggleButton . ")[$i]";
            $massActionList = '(' . $this->massActionToggleList . ")[$i]";
            if ($this->_rootElement->find($massActionButton, Locator::SELECTOR_XPATH)->isVisible()) {
                $this->_rootElement->find($massActionButton, Locator::SELECTOR_XPATH)->click();
                $this->_rootElement
                    ->find(sprintf($massActionList, $massActionSelection), Locator::SELECTOR_XPATH)
                    ->click();
                break;
            }
        }
    }

    /**
     * Peform action using the dropdown above the grid.
     *
     * @param array|string $action [array -> key = value from first select; value => value from subselect]
     * @return void
     */
    public function selectAction($action)
    {
        $actionType = is_array($action) ? key($action) : $action;
        $this->getGridHeaderElement()->find($this->actionButton)->click();
        $toggle = $this->getGridHeaderElement()->find(sprintf($this->actionList, $actionType), Locator::SELECTOR_XPATH);
        $toggle->hover();
        if ($toggle->isVisible() === false) {
            $this->getGridHeaderElement()->find($this->actionButton)->click();
        }
        $toggle->click();
        if (is_array($action)) {
            $locator = sprintf($this->actionList, end($action));
            $this->getGridHeaderElement()->find($locator, Locator::SELECTOR_XPATH)->hover();
            $this->getGridHeaderElement()->find($locator, Locator::SELECTOR_XPATH)->click();
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
            $this->_rootElement->find($this->currentPage, Locator::SELECTOR_XPATH)->setValue('');
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
                throw new \Exception("Searched item was not found\n" . print_r($item, true));
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
     * Sort grid by column.
     *
     * @param string $columnLabel
     * @return void
     */
    public function sortByColumn($columnLabel)
    {
        $this->waitLoader();
        $this->getTemplateBlock()->waitForElementNotVisible($this->loader);
        $this->_rootElement->find(sprintf($this->columnHeader, $columnLabel), Locator::SELECTOR_XPATH)->click();
        $this->waitLoader();
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
        $columnNumber = count(
            $this->_rootElement->getElements(sprintf($this->columnNumber, $headerLabel), Locator::SELECTOR_XPATH)
        );
        $selector = sprintf($this->rowById, $id) . sprintf($this->cellByHeader, $columnNumber);

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

    /**
     * Get rows data.
     *
     * @param array $columns
     * @return array
     */
    public function getRowsData(array $columns)
    {
        $data = [];
        $rows = $this->_rootElement->getElements($this->rowItem);
        foreach ($rows as $row) {
            $rowData = [];
            foreach ($columns as $columnName) {
                $rowData[$columnName] = trim($row->find('div[data-index="' . $columnName . '"]')->getText());
            }

            $data[] = $rowData;
        }

        return $data;
    }

    /**
     * Returns admin data grid header element.
     *
     * @return \Magento\Mtf\Client\ElementInterface
     */
    private function getGridHeaderElement()
    {
        return $this->_rootElement->find($this->gridHeader, Locator::SELECTOR_XPATH);
    }
}
