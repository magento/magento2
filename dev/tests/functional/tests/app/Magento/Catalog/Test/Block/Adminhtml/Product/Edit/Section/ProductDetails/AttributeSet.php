<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Section\ProductDetails;

use Magento\Mtf\Client\Element\SuggestElement;
use Magento\Mtf\Client\Locator;

/**
 * Set and Get Attribute Set on the Product form.
 */
class AttributeSet extends SuggestElement
{
    /**
     * Attribute Set locator.
     *
     * @var string
     */
    protected $attributeSet = './/div[text()="%s"]';

    /**
     * Attribute Set value locator.
     *
     * @var string
     */
    protected $attributeSetValue = '[data-role="selected-option"]';

    /**
     * Set value.
     *
     * @param string $value
     * @return void
     */
    public function setValue($value)
    {
        if (!$this->context->find(sprintf($this->attributeSet, $value), Locator::SELECTOR_XPATH)) {
            parent::setValue($value);
        }
    }

    /**
     * Get value.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->context->find($this->attributeSetValue)->getText();
    }
}
