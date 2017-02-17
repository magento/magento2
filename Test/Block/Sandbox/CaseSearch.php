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
     * @var string
     */
    private $searchInput = '#queueSearchBar';

    /**
     * @var string
     */
    private $buttonSubmit = '[type=submit]';

    /**
     * @var string
     */
    private $buttonCase = '//ul//li[contains(@class, "app-sidebar-item")][1]//a';

    /**
     * @param $searchCriteria
     */
    public function fillSearchCriteria($searchCriteria)
    {
        $this->waitForElementVisible($this->searchInput);
        $this->_rootElement->find($this->searchInput)->setValue($searchCriteria);
    }

    /**
     * @return void
     */
    public function searchCase()
    {
        $this->_rootElement->find($this->buttonSubmit)->click();
    }

    /**
     * @return void
     */
    public function selectCase()
    {
        $this->_rootElement->find($this->buttonCase, Locator::SELECTOR_XPATH)->click();
    }
}
