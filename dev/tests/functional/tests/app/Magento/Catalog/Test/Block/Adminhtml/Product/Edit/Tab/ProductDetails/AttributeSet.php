<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\ProductDetails;

use Magento\Mtf\Client\Element\SuggestElement;

/**
 * Class AttributeSet
 * Set and Get Attribute Set on the Product form
 */
class AttributeSet extends SuggestElement
{
    /**
     * Attribute Set locator
     *
     * @var string
     */
    protected $value = '.action-toggle > span';

    /**
     * Attribute Set button
     *
     * @var string
     */
    protected $actionToggle = '.action-toggle';

    /**
     * Magento loader
     *
     * @var string
     */
    protected $loader = '[data-role="loader"]';

    /**
     * Set value
     *
     * @param string $value
     * @return void
     */
    public function setValue($value)
    {
        if ($value !== $this->find($this->actionToggle)->getText()) {
            $this->find($this->actionToggle)->click();
            parent::setValue($value);
        }
        // Wait loader
        $element = $this->driver;
        $selector = $this->loader;
        $element->waitUntil(
            function () use ($element, $selector) {
                return $element->find($selector)->isVisible() == false ? true : null;
            }
        );
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->find($this->value)->getText();
    }
}
