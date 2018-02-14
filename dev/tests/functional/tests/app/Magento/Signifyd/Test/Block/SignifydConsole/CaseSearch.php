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
