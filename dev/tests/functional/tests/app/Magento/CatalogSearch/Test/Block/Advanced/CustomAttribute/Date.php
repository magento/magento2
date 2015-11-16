<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Block\Advanced\CustomAttribute;

use Magento\Mtf\Block\Form as BaseForm;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Advanced search form with custom Date attribute.
 */
class Date extends BaseForm
{
    /**
     * Selector for date from input.
     *
     * @var string
     */
    protected $dateFromSelector = '[name="%s[from]"]';

    /**
     * Selector for date to input.
     *
     * @var string
     */
    protected $dateToSelector = '[name="%s[to]"]';

    /**
     * Fill the root form.
     *
     * @param FixtureInterface $fixture
     * @param SimpleElement|null $element
     * @param array|null $mapping
     * @return $this
     */
    public function fill(FixtureInterface $fixture, SimpleElement $element = null, array $mapping = null)
    {
        $data = $fixture->getData();

        // Mapping
        $mapping = $this->dataMapping($data, $mapping);
        $attribute = $fixture->getDataFieldConfig('custom_attribute')['source']->getAttribute();
        $mappingDate['custom_attribute']['from'] = $mapping['custom_attribute'];
        $mappingDate['custom_attribute']['to'] = $mapping['custom_attribute'];
        $attributeCode = $attribute->getAttributeCode();
        $mappingDate['custom_attribute']['from']['selector'] = sprintf($this->dateFromSelector, $attributeCode);
        $mappingDate['custom_attribute']['to']['selector'] = sprintf($this->dateToSelector, $attributeCode);

        $this->_fill($mappingDate, $element);

        return $this;
    }
}
