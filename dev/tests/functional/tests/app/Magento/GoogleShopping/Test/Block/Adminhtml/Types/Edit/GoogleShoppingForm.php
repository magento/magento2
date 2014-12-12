<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\GoogleShopping\Test\Block\Adminhtml\Types\Edit;

use Mtf\Block\Form;
use Mtf\Client\Element;
use Mtf\Client\Element\Locator;

/**
 * Class GoogleShoppingForm
 * Google Shopping form
 */
class GoogleShoppingForm extends Form
{
    /**
     * Attribute options locator
     *
     * @var string
     */
    protected $attributeOptions = '//select[@id="gcontent_attribute_0_attribute"]//option';

    /**
     * Loading Mask locator
     *
     * @var string
     */
    protected $loadingMask = '//ancestor::body/div[@id="loading-mask"]';

    /**
     * Fill specified form data
     *
     * @param array $fields
     * @param Element $element
     * @return void
     */
    protected function _fill(array $fields, Element $element = null)
    {
        $context = ($element === null) ? $this->_rootElement : $element;
        foreach ($fields as $field) {
            $element = $this->getElement($context, $field);
            if ($this->mappingMode || ($element->isVisible() && !$element->isDisabled())) {
                $element->setValue($field['value']);
                $this->waitForElementNotVisible($this->loadingMask, Locator::SELECTOR_XPATH);
            }
        }
    }

    /**
     * Find Attribute in Attribute set mapping form
     *
     * @param string $attributeName
     * @return bool
     */
    public function findAttribute($attributeName)
    {
        $attributes = $this->getOptions();
        foreach ($attributes as $attribute) {
            if ($attribute == $attributeName) {
                return true;
            }
        }

        return false;
    }

    /**
     * Click "Add New Attribute" button
     *
     * @return void
     */
    public function clickAddNewAttribute()
    {
        $this->_rootElement->find('#add_new_attribute')->click();
    }

    /**
     * Getting all options in select list
     *
     * @return array
     */
    protected function getOptions()
    {
        $elements = $this->_rootElement->find($this->attributeOptions, Locator::SELECTOR_XPATH)->getElements();

        $options = [];
        foreach ($elements as $key => $element) {
            $options[$key] = $element->getText();
        }

        return $options;
    }
}
