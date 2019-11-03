<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Block\SignifydConsole;

use Magento\Mtf\Block\Form;

/**
 * Side block with case search and cases list.
 */
class CaseSearch extends Form
{
    /**
     * Css selector of search input.
     *
     * @var string
     */
    private $searchBar = '[id=queueSearchBar]';

    /**
     * Css selector of search submit button.
     *
     * @var string
     */
    private $submitButton = '[type=submit]';

    /**
     * Css selector of searched element in cases list.
     *
     * @var string
     */
    private $selectCaseLink = 'ul[case-list=cases] li[case-list-case=case] a';

    /**
     * Locator for resolving applied filters list.
     *
     * @var string
     */
    private $appliedFilters = '.app-taglist > ul > li > a';

    /**
     * Locator for loading spinner.
     *
     * @var string
     */
    private $spinner = '.cases-loading-spinner';

    /**
     * Fill search input with customer name and submit.
     *
     * @param string $customerName
     * @return void
     */
    public function searchCaseByCustomerName($customerName)
    {
        $this->resetFilters();
        $this->_rootElement->find($this->searchBar)->setValue($customerName);
        $this->_rootElement->find($this->submitButton)->click();
        $this->waitLoadingSpinner();
    }

    /**
     * Reset applied filters.
     *
     * @return void
     */
    private function resetFilters(): void
    {
        $filters = $this->_rootElement->getElements($this->appliedFilters);
        if (!empty($filters)) {
            foreach ($filters as $filter) {
                $filter->click();
                $this->waitLoadingSpinner();
            }
        }
    }

    /**
     * Wait until loading spinner disappeared.
     *
     * @return void
     */
    private function waitLoadingSpinner(): void
    {
        $this->waitForElementNotVisible($this->spinner);
    }

    /**
     * Checks if any case is visible.
     *
     * @return bool
     */
    public function isAnyCaseVisible(): bool
    {
        return $this->_rootElement->find($this->selectCaseLink)->isVisible();
    }

    /**
     * Select searched case.
     *
     * @return void
     */
    public function selectCase()
    {
        $this->_rootElement->find($this->selectCaseLink)->click();
    }

    /**
     * Waiting of case page loading.
     *
     * @return void
     */
    public function waitForLoading()
    {
        $this->waitForElementVisible($this->searchBar);
    }
}
