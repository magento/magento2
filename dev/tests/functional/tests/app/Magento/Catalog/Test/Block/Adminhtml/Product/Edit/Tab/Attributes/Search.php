<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\Attributes;

use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Mtf\Client\Driver\Selenium\Element\SuggestElement;

/**
 * Class FormAttributeSearch
 * Form Attribute Search on Product page
 */
class Search extends SuggestElement
{
    /**
     * Attribute Set locator
     *
     * @var string
     */
    protected $value = '.action-toggle > span';

    /**
     * Attribute Set button
     *
     * @var string
     */
    protected $actionToggle = '.action-toggle';

    /**
     * Search attribute result locator
     *
     * @var string
     */
    protected $searchResult = '.mage-suggest-dropdown .ui-corner-all';

    /**
     * Set value
     *
     * @param string $value
     * @return void
     */
    public function setValue($value)
    {
        $this->find($this->actionToggle)->click();
        parent::setValue($value);
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->find($this->value)->getText();
    }

    /**
     * Checking not exist attribute in search result
     *
     * @param CatalogProductAttribute $productAttribute
     * @return bool
     */
    public function isExistAttributeInSearchResult($productAttribute)
    {
        $this->find($this->actionToggle)->click();
        $this->find($this->suggest)->setValue($productAttribute->getFrontendLabel());
        $this->waitResult();
        if ($this->find($this->searchResult)->getText() == $productAttribute->getFrontendLabel()) {
            return true;
        }
        return false;
    }
}
