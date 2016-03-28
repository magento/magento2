<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Block\Advanced\CustomAttribute;

use Magento\Mtf\Block\Form as BaseForm;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Advanced search form with custom Select attribute.
 */
class Select extends BaseForm
{
    /**
     * Selector for select.
     *
     * @var string
     */
    protected $selectSelector = '[name="%s"]';

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
        $attribute = $fixture->getDataFieldConfig('custom_attribute')['source']->getAttribute();
        $mapping['custom_attribute']['selector'] = sprintf($this->selectSelector, $attribute->getAttributeCode());
        $mapping['custom_attribute']['input'] = 'select';
        $this->_fill($mapping, $element);

        return $this;
    }
}
