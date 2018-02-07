<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Block\Advanced;

use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Mtf\Block\Form as ParentForm;
use Magento\Mtf\Client\Element;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Advanced search form.
 */
class Form extends ParentForm
{
    /**
     * Search button selector.
     *
     * @var string
     */
    protected $searchButtonSelector = '.action.search';

    /**
     * Field selector select tax class.
     *
     * @var string
     */
    protected $taxClassSelector = '#tax_class_id';

    /**
     * Field selector.
     *
     * @var string
     */
    protected $fieldSelector = './/div[label and div]';

    /**
     * Label element selector.
     *
     * @var string
     */
    protected $labelSelector = 'label';

    /**
     * Selector for custom attribute.
     *
     * @var string
     */
    protected $customAttributeSelector = 'div[class*="%s"]';

    /**
     * Submit search form.
     *
     * @return void
     */
    public function submit()
    {
        $this->_rootElement->find($this->searchButtonSelector)->click();
    }

    /**
     * Fill the root form.
     *
     * @param FixtureInterface $fixture
     * @param SimpleElement|null $element
     * @return $this
     */
    public function fill(FixtureInterface $fixture, SimpleElement $element = null)
    {
        // Prepare price data
        $data = $fixture->getData();
        if (isset($data['price'])) {
            $data = array_merge($data, $data['price']);
            unset($data['price']);
        }

        // Mapping
        $mapping = $this->dataMapping($data);
        $attributeType = $attributeCode = '';
        if ($fixture->hasData('custom_attribute')) {
            /** @var CatalogProductAttribute $attribute */
            $attribute = $fixture->getDataFieldConfig('custom_attribute')['source']->getAttribute();
            $attributeType = $attribute->getFrontendInput();
            $attributeCode = $attribute->getAttributeCode();
        }
        if ($this->hasRender($attributeType)) {
            $element = $this->_rootElement->find(sprintf($this->customAttributeSelector, $attributeCode));
            $arguments = ['fixture' => $fixture, 'element' => $element, 'mapping' => $mapping];
            $this->callRender($attributeType, 'fill', $arguments);
        } else {
            $this->_fill($mapping, $element);
        }

        return $this;
    }

    /**
     * Get form fields.
     *
     * @return array
     */
    public function getFormLabels()
    {
        $labels = [];
        $elements = $this->_rootElement->getElements($this->fieldSelector, Locator::SELECTOR_XPATH);
        foreach ($elements as $element) {
            $labels[] = $element->find($this->labelSelector)->getText();
        }
        return $labels;
    }
}
