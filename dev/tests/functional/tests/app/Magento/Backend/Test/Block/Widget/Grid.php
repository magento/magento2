<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Backend\Test\Block\Widget;

use Mtf\Block\Block;
use Mtf\Client\Element;
use Mtf\Factory\Factory;
use Mtf\Client\Element\Locator;

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
     * Selector for action expand Filter
     *
     * @var string
     */
    protected $filterOpen = '.action.filters-toggle';

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
        $this->resetFilter();
        $this->prepareForSearch($filter);
        $this->_rootElement->find($this->searchButton, Locator::SELECTOR_CSS)->click();
        $this->getTemplateBlock()->waitLoader();
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
        $expandFilterButton = $this->_rootElement->find($this->filterOpen, Locator::SELECTOR_CSS);
        if ($expandFilterButton->isVisible()) {
            $expandFilterButton->click();
        }
        $this->_rootElement->find($this->resetButton, Locator::SELECTOR_CSS)->click();
        $this->getTemplateBlock()->waitLoader();
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
        if ($isSearchable) {
            $this->search($filter);
        }
        $location = '//div[@class="grid"]//tr[';
        $rowTemplate = 'td[contains(text(),normalize-space("%s"))]';
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
     * Check if specific row exists in grid
     *
     * @param array $filter
     * @param bool $isSearchable
     * @param bool $isStrict
     * @return bool
     */
    public function isRowVisible(array $filter, $isSearchable = true, $isStrict = true)
    {
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
        $sortBlock = $this->_rootElement->find(sprintf($this->sortLink, $field, $sort));
        if ($sortBlock->isVisible()) {
            $sortBlock->click();
            $this->getTemplateBlock()->waitLoader();
        }
        $this->reinitRootElement();
    }
}
