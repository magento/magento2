<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\ProductDetails;

use Magento\Mtf\Client\Element\SuggestElement;
use Magento\Mtf\Client\Locator;

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
     * Page header selector.
     *
     * @var string
     */
    protected $header = 'header';

    /**
     * Set value
     *
     * @param string $value
     * @return void
     */
    public function setValue($value)
    {
        if ($value !== $this->find($this->actionToggle)->getText()) {
            $this->eventManager->dispatchEvent(['set_value'], [__METHOD__, $this->getAbsoluteSelector()]);
            $this->find($this->actionToggle)->click();
            $this->clear();
            if ($value == '') {
                return;
            }
            foreach (str_split($value) as $symbol) {
                $this->keys([$symbol]);
                $searchedItem = $this->find(sprintf($this->resultItem, $value), Locator::SELECTOR_XPATH);
                if ($searchedItem->isVisible()) {
                    try {
                        $searchedItem->hover();
                        $this->driver->find($this->header)->hover();
                        $searchedItem->click();
                        break;
                    } catch (\Exception $e) {
                        // In parallel run on windows change the focus is lost on element
                        // that causes disappearing of category suggest list.
                    }
                }
            }
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
