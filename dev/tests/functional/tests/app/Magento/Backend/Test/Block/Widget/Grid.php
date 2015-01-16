<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Block\Widget;

use Mtf\Block\Block;
use Mtf\Client\Element;
use Mtf\Client\Element\Locator;
use Mtf\Factory\Factory;

/**
 * Abstract class Grid
 * Basic grid actions
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
abstract class Grid extends Block
{
    /**
     * Filters array mapping
     *
     * @var array
     */
    protected $filters = [];

    /**
     * Locator value for 'Search' button
     *
     * @var string
     */
    protected $searchButton = '[title=Search][class*=action]';

    /**
     * Locator for 'Sort' link
     *
     * @var string
     */
    protected $sortLink = "[name='%s'][title='%s']";

    /**
     * Locator value for 'Reset' button
     *
     * @var string
     */
    protected $resetButton = '[title="Reset Filter"][class*=action]';

    /**
     * The first row in grid. For this moment we suggest that we should strictly define what we are going to search
     *
     * @var string
     */
    protected $rowItem = 'tbody tr';

    /**
     * Locator value for link in action column
     *
     * @var string
     */
    protected $editLink = 'td[class*=col-action] a';

    /**
     * An element locator which allows to select entities in grid
     *
     * @var string
     */
    protected $selectItem = 'tbody tr .col-select';

    /**
     * 'Select All' link
     *
     * @var string
     */
    protected $selectAll = '.massaction a[onclick*=".selectAll()"]';

    /**
     * Massaction dropdown
     *
     * @var string
     */
    protected $massactionSelect = '[id*=massaction-select]';

    /**
     * Massaction dropdown
     *
     * @var string
     */
    protected $massactionAction = '#massaction-select';

    /**
     * Massaction 'Submit' button
     *
     * @var string
     */
    protected $massactionSubmit = '[id*=massaction-form] button';

    /**
     * Backend abstract block
     *
     * @var string
     */
    protected $templateBlock = './ancestor::body';

    /**
     * Selector of element to wait for. If set by child will wait for element after action
     *
     * @var string
     */
    protected $waitForSelector;

    /**
     * Locator type of waitForSelector
     *
     * @var Locator
     */
    protected $waitForSelectorType = Locator::SELECTOR_CSS;

    /**
     * Wait for should be for visibility or not?
     *
     * @var boolean
     */
    protected $waitForSelectorVisible = true;

    /**
     * Selector for action option select
     *
     * @var string
     */
    protected $option = '[name="status"]';

    /**
     * Filter button
     *
     * @var string
     */
    protected $filterButton = '.action.filters-toggle';

    /**
     * Active class
     *
     * @var string
     */
    protected $active = '.active';

    /**
     * Base part of row locator template for getRow() method
     *
     * @var string
     */
    protected $location = '//div[@class="grid"]//tr[';

    /**
     * Secondary part of row locator template for getRow() method
     *
     * @var string
     */
    protected $rowTemplate = 'td[contains(text(),normalize-space("%s"))]';

    /**
     * Secondary part of row locator template for getRow() method with strict option
     *
     * @var string
     */
    protected $rowTemplateStrict = 'td[text()[normalize-space()="%s"]]';

    /**
     * Magento grid loader
     *
     * @var string
     */
    protected $loader = '[data-role="spinner"]';

    /**
     * Locator for next page action
     *
     * @var string
     */
    protected $actionNextPage = '.pager .action-next';

    /**
     * Locator for disabled next page action
     *
     * @var string
     */
    protected $actionNextPageDisabled = '.pager .action-next.disabled';

    /**
     * First row selector
     *
     * @var string
     */
    protected $firstRowSelector = '';

    /**
     * Get backend abstract block
     *
     * @return \Magento\Backend\Test\Block\Template
     */
    protected function getTemplateBlock()
    {
        return Factory::getBlockFactory()->getMagentoBackendTemplate(
            $this->_rootElement->find($this->templateBlock, Locator::SELECTOR_XPATH)
        );
    }

    /**
     * Prepare data to perform search, fill in search filter
     *
     * @param array $filters
     * @throws \Exception
     */
    private function prepareForSearch(array $filters)
    {
        foreach ($filters as $key => $value) {
            if (isset($this->filters[$key])) {
                $selector = $this->filters[$key]['selector'];
                $strategy = isset($this->filters[$key]['strategy'])
                    ? $this->filters[$key]['strategy']
                    : Locator::SELECTOR_CSS;
                $typifiedElement = isset($this->filters[$key]['input'])
                    ? $this->filters[$key]['input']
                    : null;
                $this->_rootElement->find($selector, $strategy, $typifiedElement)->setValue($value);
            } else {
                throw new \Exception('Such column is absent in the grid or not described yet.');
            }
        }
    }

    /**
     * Search item via grid filter
     *
     * @param array $filter
     */
    public function search(array $filter)
    {
        $this->openFilterBlock();
        $this->resetFilter();
        $this->prepareForSearch($filter);
        $this->_rootElement->find($this->searchButton, Locator::SELECTOR_CSS)->click();
        $this->waitLoader();
        $this->reinitRootElement();
    }

    /**
     * Search item and open it
     *
     * @param array $filter
     * @throws \Exception
     */
    public function searchAndOpen(array $filter)
    {
        $this->openFilterBlock();
        $this->search($filter);
        $rowItem = $this->_rootElement->find($this->rowItem, Locator::SELECTOR_CSS);
        if ($rowItem->isVisible()) {
            $rowItem->find($this->editLink, Locator::SELECTOR_CSS)->click();
            $this->waitForElement();
        } else {
            throw new \Exception('Searched item was not found.');
        }
    }

    /**
     * Wait loader
     *
     * @return void
     */
    protected function waitLoader()
    {
        $browser = $this->browser;
        $selector = $this->loader;
        $browser->waitUntil(
            function () use ($browser, $selector) {
                $productSavedMessage = $browser->find($selector);
                return $productSavedMessage->isVisible() == false ? true : null;
            }
        );
        $this->getTemplateBlock()->waitLoader();
    }

    /**
     * Method that waits for the configured selector using class attributes.
     */
    protected function waitForElement()
    {
        if (!empty($this->waitForSelector)) {
            if ($this->waitForSelectorVisible) {
                $this->getTemplateBlock()->waitForElementVisible($this->waitForSelector, $this->waitForSelectorType);
            } else {
                $this->getTemplateBlock()->waitForElementNotVisible($this->waitForSelector, $this->waitForSelectorType);
            }
        }
    }

    /**
     * Search for item and select it
     *
     * @param array $filter
     * @throws \Exception
     */
    public function searchAndSelect(array $filter)
    {
        $this->openFilterBlock();
        $this->search($filter);
        $selectItem = $this->_rootElement->find($this->selectItem);
        if ($selectItem->isVisible()) {
            $selectItem->click();
        } else {
            throw new \Exception('Searched item was not found.');
        }
    }

    /**
     * Press 'Reset' button
     */
    public function resetFilter()
    {
        $this->openFilterBlock();
        $this->_rootElement->find($this->resetButton, Locator::SELECTOR_CSS)->click();
        $this->waitLoader();
        $this->reinitRootElement();
    }

    /**
     * Perform selected massaction over checked items
     *
     * @param array $items
     * @param array|string $action [array -> key = value from first select; value => value from subselect]
     * @param bool $acceptAlert [optional]
     * @param string $massActionSelection [optional]
     * @return void
     */
    public function massaction(array $items, $action, $acceptAlert = false, $massActionSelection = '')
    {
        if (!is_array($action)) {
            $action = [$action => '-'];
        }
        foreach ($items as $item) {
            $this->searchAndSelect($item);
        }
        if ($massActionSelection) {
            $this->_rootElement->find($this->massactionAction, Locator::SELECTOR_CSS, 'select')
                ->setValue($massActionSelection);
        }
        $actionType = key($action);
        $this->_rootElement->find($this->massactionSelect, Locator::SELECTOR_CSS, 'select')->setValue($actionType);
        if (isset($action[$actionType]) && $action[$actionType] != '-') {
            $this->_rootElement->find($this->option, Locator::SELECTOR_CSS, 'select')->setValue($action[$actionType]);
        }
        $this->massActionSubmit($acceptAlert);
    }

    /**
     * Submit mass actions
     *
     * @param bool $acceptAlert
     * @return void
     */
    protected function massActionSubmit($acceptAlert)
    {
        $this->_rootElement->find($this->massactionSubmit, Locator::SELECTOR_CSS)->click();
        if ($acceptAlert) {
            $this->_rootElement->acceptAlert();
        }
    }

    /**
     * Obtain specific row in grid
     *
     * @param array $filter
     * @param bool $isSearchable
     * @param bool $isStrict
     * @return Element
     */
    protected function getRow(array $filter, $isSearchable = true, $isStrict = true)
    {
        $this->openFilterBlock();
        if ($isSearchable) {
            $this->search($filter);
        }
        $location = '//div[@class="grid"]//tr[';
        $rowTemplate = 'td[contains(.,normalize-space("%s"))]';
        if ($isStrict) {
            $rowTemplate = 'td[text()[normalize-space()="%s"]]';
        }
        $rows = [];
        foreach ($filter as $value) {
            $rows[] = sprintf($rowTemplate, $value);
        }
        $location = $location . implode(' and ', $rows) . ']';
        return $this->_rootElement->find($location, Locator::SELECTOR_XPATH);
    }

    /**
     * Get rows data
     *
     * @param array $columns
     * @return array
     */
    public function getRowsData(array $columns)
    {
        $data = [];
        do {
            $rows = $this->_rootElement->find($this->rowItem)->getElements();
            foreach ($rows as $row) {
                $rowData = [];
                foreach ($columns as $columnName) {
                    $rowData[$columnName] = trim($row->find('.col-' . $columnName)->getText());
                }

                $data[] = $rowData;
            }
        } while ($this->nextPage());

        return $data;
    }

    /**
     * Check if specific row exists in grid
     *
     * @param array $filter
     * @param bool $isSearchable
     * @param bool $isStrict
     * @return bool
     */
    public function isRowVisible(array $filter, $isSearchable = true, $isStrict = true)
    {
        $this->openFilterBlock();
        return $this->getRow($filter, $isSearchable, $isStrict)->isVisible();
    }

    /**
     * Sort grid by field
     *
     * @param $field
     * @param string $sort
     */
    public function sortGridByField($field, $sort = "desc")
    {
        $this->openFilterBlock();
        $sortBlock = $this->_rootElement->find(sprintf($this->sortLink, $field, $sort));
        if ($sortBlock->isVisible()) {
            $sortBlock->click();
            $this->waitLoader();
        }
        $this->reinitRootElement();
    }

    /**
     * Open Filter Block
     *
     * @return void
     */
    protected function openFilterBlock()
    {
        $button = $this->_rootElement->find($this->filterButton);
        if ($button->isVisible() && !$this->_rootElement->find($this->filterButton . $this->active)->isVisible()) {
            $button->click();
            $browser = $this->_rootElement;
            $selector = $this->searchButton;
            $browser->waitUntil(
                function () use ($browser, $selector) {
                    return $browser->find($selector)->isVisible() ? true : null;
                }
            );
        }
    }

    /**
     * Click to next page action link
     *
     * @return bool
     */
    protected function nextPage()
    {
        if ($this->_rootElement->find($this->actionNextPageDisabled)->isVisible()) {
            return false;
        }
        $this->_rootElement->find($this->actionNextPage)->click();
        $this->waitLoader();
        return true;
    }

    /**
     * Check whether first row is visible
     *
     * @return bool
     */
    public function isFirstRowVisible()
    {
        return $this->_rootElement->find($this->firstRowSelector, Locator::SELECTOR_XPATH)->isVisible();
    }

    /**
     * Open first item in grid
     *
     * @return void
     */
    public function openFirstRow()
    {
        $this->_rootElement->find($this->firstRowSelector, Locator::SELECTOR_XPATH)->click();
    }
}
