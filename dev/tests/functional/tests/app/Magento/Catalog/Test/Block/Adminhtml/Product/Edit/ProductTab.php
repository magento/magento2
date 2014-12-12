<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit;

use Magento\Backend\Test\Block\Widget\Tab;
use Mtf\Client\Element\Locator;

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
    protected $attributeSearch = "//div[contains(@data-role, '%s')]//*[@id='product-attribute-search-container']";

    /**
     * Selector for 'New Attribute' button.
     *
     * @var string
     */
    protected $newAttributeButton = '[id^="create_attribute"]';

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
        $this->_rootElement->find(sprintf($this->attributeSearch, $tabName), Locator::SELECTOR_XPATH)->click();
        $this->_rootElement->find($this->newAttributeButton)->click();
    }
}
