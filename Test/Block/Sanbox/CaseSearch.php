<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Block\Sanbox;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Locator;

class CaseSearch extends Form
{
    /**
     * Submit button for Signifyd case search.
     *
     * @var string
     */
    private $searchInput = '#queueSearchBar';
    private $buttonSubmit = '[type=submit]';
    private $buttonCase = '//ul//li[contains(@class, "app-sidebar-item")][1]//a';

    public function fillSearchCriteria($searchCriteria)
    {
        $this->_rootElement->find($this->searchInput)->setValue($searchCriteria);
    }

    /**
     * Search Signifyd case.
     *
     * @return void
     */
    public function searchCase()
    {
        $this->_rootElement->find($this->buttonSubmit)->click();
    }

    public function selectCase()
    {
        $this->_rootElement->find($this->buttonCase, Locator::SELECTOR_XPATH)->click();
    }
}
