<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit;

use Magento\Mtf\Client\Locator;
use Magento\Backend\Test\Block\Widget\Tab;
use Magento\Catalog\Test\Block\Adminhtml\Product\Attribute\Edit;

/**
 * General class for tabs on product FormTabs with "Add attribute" button.
 */
class ProductTab extends Tab
{
    /**
     * Attribute Search locator the Product page.
     *
     * @var string
     */
    protected $attributeSearch = "//div[contains(@data-role, 'product-details')]//*[@data-toggle='dropdown']/span";

    /**
     * Selector for 'New Attribute' button.
     *
     * @var string
     */
    protected $newAttributeButton = '[id^="create_attribute"]';

    /**
     * Selector for search input field.
     *
     * @var string
     */
    protected $searchAttribute = "//input[@data-role='product-attribute-search']";

    /**
     * Fixture mapping.
     *
     * @param array|null $fields
     * @param string|null $parent
     * @return array
     */
    protected function dataMapping(array $fields = null, $parent = null)
    {
        if (isset($fields['custom_attribute'])) {
            $this->placeholders = ['attribute_code' => $fields['custom_attribute']['value']['code']];
            $this->applyPlaceholders();
        }
        return parent::dataMapping($fields, $parent);
    }

    /**
     * Click on 'New Attribute' button.
     *
     * @param string $tabName
     * @return void
     */
    public function addNewAttribute($tabName)
    {
        $element = $this->_rootElement;
        $selector = sprintf($this->attributeSearch, $tabName);
        $element->waitUntil(
            function () use ($element, $selector) {
                return $element->find($selector, Locator::SELECTOR_XPATH)->isVisible() ? true : null;
            }
        );
        $addAttributeToggle = $element->find($selector, Locator::SELECTOR_XPATH);
        $addAttributeToggle->click();
        if (!$addAttributeToggle->find($this->newAttributeButton)->isVisible()) {
            $element->find($this->searchAttribute, Locator::SELECTOR_XPATH)->click();
        }
        $element->find($this->newAttributeButton)->click();
    }
}
