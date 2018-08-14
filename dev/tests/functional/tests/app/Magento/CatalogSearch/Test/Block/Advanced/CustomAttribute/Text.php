<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Block\Advanced\CustomAttribute;

use Magento\Mtf\Block\Form as BaseForm;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Advanced search form with custom Text attribute.
 */
class Text extends BaseForm
{
    /**
     * Selector for text input.
     *
     * @var string
     */
    protected $inputSelector = '[name="%s"]';

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
        $mapping['custom_attribute']['selector'] = sprintf($this->inputSelector, $attribute->getAttributeCode());
        $this->_fill($mapping, $element);

        return $this;
    }
}
