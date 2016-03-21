<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Block\Adminhtml\Product;

use Magento\Mtf\Client\Element;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Block\Form as ParentForm;

/**
 * Choose affected attribute set dialog popup window.
 */
class AffectedAttributeSet extends ParentForm
{
    /**
     * 'Confirm' button locator.
     *
     * @var string
     */
    protected $confirmButton = '[data-index="confirm_button"]';

    /**
     * Add configurable attributes to the New Attribute Set.
     *
     * @var string
     */
    protected $affectedAttributeSetNew = 'input[data-index="affectedAttributeSetNew"]';

    /**
     * Fill popup form.
     *
     * @param FixtureInterface $product
     * @param SimpleElement|null $element [optional]
     * @return $this
     */
    public function fill(FixtureInterface $product, SimpleElement $element = null)
    {
        $affectedAttributeSet = $product->getData('affected_attribute_set');

        if ($affectedAttributeSet) {
            $fields = ['new_attribute_set_name' => $affectedAttributeSet];
            $mapping = $this->dataMapping($fields);

            $this->_rootElement->find($this->affectedAttributeSetNew)->click();
            $this->_fill($mapping, $element);
        }

        return $this;
    }

    /**
     * Click confirm button.
     *
     * @return void
     */
    public function confirm()
    {
        $this->_rootElement->find($this->confirmButton, Locator::SELECTOR_CSS)->click();
    }
}
