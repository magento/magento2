<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Checkout\Test\Block\Cart;

use Mtf\Block\Block;
use Mtf\Client\Element\Locator;

/**
 * Class CartItem
 * Product item block on checkout page
 */
class CartItem extends Block
{
    /**
     *  Selector for options block
     *
     * @var string
     */
    protected $optionsBlock = './/dl[@class="cart-item-options"]';

    /**
     * Selector for unit price
     *
     * @var string
     */
    protected $price = './/td[@class="col price"]/*[@class="excl tax"]/span';

    /**
     * Quantity input selector
     *
     * @var string
     */
    protected $qty = './/input[@type="number" and @title="Qty"]';

    /**
     * Cart item sub-total xpath selector
     *
     * @var string
     */
    protected $subtotalPrice = './/td[@class="col subtotal"]//*[@class="excl tax"]//span[@class="price"]';

    /**
     * Get bundle options
     *
     * @var string
     */
    protected $bundleOptions = './/dl[contains(@class, "cart-item-options")]/dd[%d]/span[@class="price"][%d]';

    /**
     * Get product price
     *
     * @return string
     */
    public function getPrice()
    {
        $cartProductPrice = $this->_rootElement->find($this->price, Locator::SELECTOR_XPATH)->getText();
        return $this->escapeCurrency($cartProductPrice);
    }

    /**
     * Set product quantity
     *
     * @param int $qty
     * @return void
     */
    public function setQty($qty)
    {
        $this->_rootElement->find($this->qty, Locator::SELECTOR_XPATH)->setValue($qty);
    }

    /**
     * Get product quantity
     *
     * @return string
     */
    public function getQty()
    {
        return $this->_rootElement->find($this->qty, Locator::SELECTOR_XPATH)->getValue();
    }

    /**
     * Get sub-total for the specified item in the cart
     *
     * @return string
     */
    public function getSubtotalPrice()
    {
        $price = $this->_rootElement->find($this->subtotalPrice, Locator::SELECTOR_XPATH)->getText();
        return $this->escapeCurrency($price);
    }

    /**
     * Method that escapes currency symbols
     *
     * @param string $price
     * @return string
     */
    protected function escapeCurrency($price)
    {
        preg_match("/^\\D*\\s*([\\d,\\.]+)\\s*\\D*$/", $price, $matches);
        return (isset($matches[1])) ? $matches[1] : null;
    }

    /**
     * Get product options in the cart
     *
     * @return string
     */
    public function getOptions()
    {
        $optionsBlock = $this->_rootElement->find($this->optionsBlock, Locator::SELECTOR_XPATH);
        if (!$optionsBlock->isVisible()) {
            return '';
        }
        return $optionsBlock->getText();
    }

    /**
     * Get product options name in the cart
     *
     * @return string
     */
    public function getOptionsName()
    {
        $optionsName = $this->_rootElement->find($this->optionsBlock . '//dt', Locator::SELECTOR_XPATH);
        if (!$optionsName->isVisible()) {
            return '';
        }
        return $optionsName->getText();
    }

    /**
     * Get product options value in the cart
     *
     * @return string
     */
    public function getOptionsValue()
    {
        $optionsValue = $this->_rootElement->find($this->optionsBlock . '//dd', Locator::SELECTOR_XPATH);
        if (!$optionsValue->isVisible()) {
            return '';
        }
        return $optionsValue->getText();
    }

    /**
     * Get item Bundle options
     *
     * @param int $index
     * @param int $itemIndex [optional]
     * @param string $currency [optional]
     * @return string
     */
    public function getPriceBundleOptions($index, $itemIndex = 1, $currency = '$')
    {
        $formatPrice = sprintf($this->bundleOptions, $index, $itemIndex);
        return trim($this->_rootElement->find($formatPrice, Locator::SELECTOR_XPATH)->getText(), $currency);
    }
}
