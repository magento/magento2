<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Test\Block\Adminhtml\Promo\Catalog\Edit\Section;

use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Mtf\Client\Locator;
use Magento\Ui\Test\Block\Adminhtml\Section;

/**
 * Form section for specifying catalog price rule conditions.
 */
class Conditions extends Section
{
    /**
     * Add button.
     *
     * @var string
     */
    protected $addButton = '.rule-param-new-child a';

    /**
     * Locator for specific conditions.
     *
     * @var string
     */
    protected $conditionFormat = '//*[@id="conditions__1__new_child"]//option[contains(.,"%s")]';

    /**
     * Check if attribute is available in conditions.
     *
     * @param CatalogProductAttribute $attribute
     * @return bool
     */
    public function isAttributeInConditions(CatalogProductAttribute $attribute)
    {
        $this->_rootElement->find($this->addButton)->click();
        return $this->_rootElement->find(
            sprintf($this->conditionFormat, $attribute->getFrontendLabel()),
            Locator::SELECTOR_XPATH
        )->isVisible();
    }
}
