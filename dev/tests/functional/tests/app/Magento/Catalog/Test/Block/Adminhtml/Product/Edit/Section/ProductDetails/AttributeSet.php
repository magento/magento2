<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
     * Selector item of search result.
     *
     * @var string
     */
    protected $resultItem = './/label[contains(@class, "admin__action-multiselect-label")]/span[text() = "%s"]';

    /**
     * Set value.
     *
     * @param string $value
     * @return void
     */
    public function setValue($value)
    {
        if (!$this->find(sprintf($this->attributeSet, $value), Locator::SELECTOR_XPATH)->isVisible()) {
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
        return $this->find($this->attributeSetValue)->getText();
    }
}
