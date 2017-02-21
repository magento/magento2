<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Block\Sandbox;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Locator;

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
     * XPath selector of first searched element in list.
     *
     * @var string
     */
    private $selectCaseLink = '//ul//li[contains(@class, "app-sidebar-item")][1]//a';

    /**
     * Fill search input with customer name and submit.
     *
     * @param string $customerName
     * @return void
     */
    public function searchCaseByCustomerName($customerName)
    {
        $this->_rootElement->find($this->searchBar)->setValue($customerName);
        $this->_rootElement->find($this->submitButton)->click();
    }

    /**
     * Select searched case.
     *
     * @return void
     */
    public function selectCase()
    {
        $this->_rootElement->find($this->selectCaseLink, Locator::SELECTOR_XPATH)->click();
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
