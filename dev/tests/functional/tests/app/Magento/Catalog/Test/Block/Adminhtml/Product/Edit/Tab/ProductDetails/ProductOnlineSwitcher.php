<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\ProductDetails;

use Magento\Mtf\Client\Locator;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Class ProductOnlineSwitcher
 * Typified element class for product status element
 */
class ProductOnlineSwitcher extends SimpleElement
{
    /**
     * CSS locator button status of the product
     *
     * @var string
     */
    protected $onlineSwitcher = '#product-online-switcher%s + [for="product-online-switcher"]';

    /**
     * Selector for top page click.
     *
     * @var string
     */
    protected $topPage = './ancestor::body//*[contains(@class,"page-header")]';

    /**
     * Set value
     *
     * @param string $value
     * @return void
     * @throws \Exception
     */
    public function setValue($value)
    {
        if (!$this->find(sprintf($this->onlineSwitcher, ''))->isVisible()) {
            throw new \Exception("Can't find product online switcher.");
        }
        if (($value === 'Product offline' && $this->find(sprintf($this->onlineSwitcher, ':checked'))->isVisible())
            || ($value === 'Product online'
                && $this->find(sprintf($this->onlineSwitcher, ':not(:checked)'))->isVisible()
            )
        ) {
            $this->find($this->topPage, Locator::SELECTOR_XPATH)->click();
            $this->find(sprintf($this->onlineSwitcher, ''))->click();
        }
    }

    /**
     * Get value
     *
     * @return string
     * @throws \Exception
     */
    public function getValue()
    {
        if (!$this->find(sprintf($this->onlineSwitcher, ''))->isVisible()) {
            throw new \Exception("Can't find product online switcher.");
        }
        if ($this->find(sprintf($this->onlineSwitcher, ':checked'))->isVisible()) {
            return 'Product online';
        }
        return 'Product offline';
    }
}
