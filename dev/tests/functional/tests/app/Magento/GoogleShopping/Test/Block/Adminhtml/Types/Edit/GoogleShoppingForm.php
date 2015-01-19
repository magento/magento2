<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
     * Locator for root elements
     *
     * @var string
     */
    protected $loaderRootLocator = 'body';

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
                $this->blockFactory->create(
                    'Magento\Backend\Test\Block\Template',
                    ['element' => $this->browser->find($this->loaderRootLocator)]
                )->waitLoader();
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
