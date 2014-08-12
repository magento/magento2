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
namespace Magento\Checkout\Test\Block;

use Exception;
use Mtf\Block\Block;
use Mtf\Factory\Factory;
use Mtf\Client\Element\Locator;
use Magento\Catalog\Test\Fixture\Product;
use Magento\Catalog\Test\Fixture\SimpleProduct;
use Magento\Catalog\Test\Fixture\ConfigurableProduct;
use Magento\Checkout\Test\Block\Onepage\Link;
use Mtf\Fixture\FixtureInterface;

/**
 * Class Cart
 * Shopping cart block
 */
class Cart extends Block
{
    /**
     * Proceed to checkout block
     *
     * @var string
     */
    protected $onepageLinkBlock = '.action.primary.checkout';

    /**
     * 'Clear Shopping Cart' button
     *
     * @var string
     */
    protected $clearShoppingCart = '#empty_cart_button';

    /**
     * Cart item sub-total xpath selector
     *
     * @var string
     */
    protected $itemSubTotalSelector = '//td[@class="col subtotal"]//*[@class="excl tax"]//span[@class="price"]';

    /**
     * Cart item unit price xpath selector
     *
     * @var string
     */
    protected $itemUnitPriceSelector = '//td[@class="col price"]//*[@class="excl tax"]//span[@class="price"]';

    /**
     * Unit Price value
     *
     * @var string
     */
    protected $cartProductPrice = '//tr[string(td/div/strong/a)="%s"]/td[@class="col price"]/*[@class="excl tax"]/span';

    /**
     * 'Update Shopping Cart' button
     *
     * @var string
     */
    protected $updateShoppingCart = '[name="update_cart_action"]';

    /**
     * Quantity input selector
     *
     * @var string
     */
    protected $productQty = '//input[@type="number" and @title="Qty"]';

    /**
     * Cart item selector
     *
     * @var string
     */
    protected $cartItem = './/tr[td//*[contains(.,"%s")]]';

    /**
     * Get bundle options
     *
     * @var string
     */
    protected $bundleOptions = './/dl[contains(@class, "cart-item-options")]/dd[%d]/span[@class="price"][%d]';

    /**
     * Get sub-total for the specified item in the cart
     *
     * @param SimpleProduct $product
     * @return string
     */
    public function getCartItemSubTotal($product)
    {
        $selector = sprintf($this->cartItem, $this->getProductName($product)) . $this->itemSubTotalSelector;
        return $this->_rootElement->find($selector, Locator::SELECTOR_XPATH)->getText();
    }

    /**
     * Get sub-total for the specified item in the cart by product name
     *
     * @param string $productName
     * @return string
     */
    public function getCartItemSubTotalByProductName($productName)
    {
        $selector = sprintf($this->cartItem, $productName) . $this->itemSubTotalSelector;
        $itemSubtotal = $this->_rootElement->find($selector, Locator::SELECTOR_XPATH)->getText();
        return $this->escapeCurrency($itemSubtotal);
    }

    /**
     * Get unit price for the specified item in the cart
     *
     * @param FixtureInterface $product
     * @param string $currency
     * @return float
     */
    public function getCartItemUnitPrice($product, $currency = '$')
    {
        $selector = sprintf($this->cartItem, $this->getProductName($product)) . $this->itemUnitPriceSelector;
        $prices = explode("\n", trim($this->_rootElement->find($selector, Locator::SELECTOR_XPATH)->getText()));
        return floatval(trim($prices[0], $currency));
    }

    /**
     * Get product options in the cart
     *
     * @param Product $product
     * @return string
     */
    public function getCartItemOptions($product)
    {
        $selector = '//tr[string(td/div/strong/a)="' . $this->getProductName($product)
            . '"]//dl[@class="cart item options"]';

        $optionsBlock = $this->_rootElement->find($selector, Locator::SELECTOR_XPATH);
        if (!$optionsBlock->isVisible()) {
            return '';
        }
        return $optionsBlock->getText();
    }

    /**
     * Get product options value in the cart by product name
     *
     * @param string $productName
     * @return string
     */
    public function getCartItemOptionsNameByProductName($productName)
    {
        $selector = '//tr[string(td/div/strong/a)="' . $productName . '"]//dl[@class="cart-item-options"]//dt';

        $optionsBlock = $this->_rootElement->find($selector, Locator::SELECTOR_XPATH);
        if (!$optionsBlock->isVisible()) {
            return '';
        }
        return $optionsBlock->getText();
    }

    /**
     * Get product options value in the cart by product name
     *
     * @param string $productName
     * @return string
     */
    public function getCartItemOptionsValueByProductName($productName)
    {
        $selector = '//tr[string(td/div/strong/a)="' . $productName . '"]//dl[@class="cart-item-options"]//dd';

        $optionsBlock = $this->_rootElement->find($selector, Locator::SELECTOR_XPATH);
        if (!$optionsBlock->isVisible()) {
            return '';
        }
        return $optionsBlock->getText();
    }

    /**
     * Get proceed to checkout block
     *
     * @return Link
     */
    public function getOnepageLinkBlock()
    {
        return Factory::getBlockFactory()->getMagentoCheckoutOnepageLink(
            $this->_rootElement->find($this->onepageLinkBlock, Locator::SELECTOR_CSS)
        );
    }

    /**
     * Press 'Check out with PayPal' button
     *
     * @return void
     */
    public function paypalCheckout()
    {
        $this->_rootElement->find('[data-action=checkout-form-submit]', Locator::SELECTOR_CSS)->click();
    }

    /**
     * Returns the total discount price
     *
     * @var string
     * @return string
     * @throws Exception
     */
    public function getDiscountTotal()
    {
        $element = $this->_rootElement->find(
            '//table[@id="shopping-cart-totals-table"]' .
            '//tr[normalize-space(td)="Discount"]' .
            '//td[@class="amount"]//span[@class="price"]',
            Locator::SELECTOR_XPATH
        );
        if (!$element->isVisible()) {
            throw new Exception('Error could not find the Discount Total in the HTML');
        }
        return $element->getText();
    }

    /**
     * Clear shopping cart
     *
     * @return void
     */
    public function clearShoppingCart()
    {
        $clearShoppingCart = $this->_rootElement->find($this->clearShoppingCart);
        if ($clearShoppingCart->isVisible()) {
            $clearShoppingCart->click();
        }
    }

    /**
     * Check if a product has been successfully added to the cart
     *
     * @param Product $product
     * @return boolean
     */
    public function isProductInShoppingCart($product)
    {
        return $this->_rootElement->find(
            sprintf($this->cartItem, $this->getProductName($product)),
            Locator::SELECTOR_XPATH
        )->isVisible();
    }

    /**
     * Return the name of the specified product.
     *
     * @param FixtureInterface $product
     * @return string
     */
    private function getProductName($product)
    {
        $productName = $product->getName();
        if ($product instanceof ConfigurableProduct) {
            $productOptions = $product->getProductOptions();
            if (!empty($productOptions)) {
                $productName = $productName . '")] and *[contains(.,"' . current($productOptions);
            }
        }
        return $productName;
    }

    /**
     * Get product price "Unit Price" by product name
     *
     * @param $productName
     * @return string
     */
    public function getProductPriceByName($productName)
    {
        $priceSelector = sprintf($this->cartProductPrice, $productName);
        $cartProductPrice = $this->_rootElement->find($priceSelector, Locator::SELECTOR_XPATH)->getText();
        return $this->escapeCurrency($cartProductPrice);
    }

    /**
     * Update shopping cart
     *
     * @return void
     */
    public function updateShoppingCart()
    {
        $this->_rootElement->find($this->updateShoppingCart, Locator::SELECTOR_CSS)->click();
    }

    /**
     * Set product quantity
     *
     * @param string $productName
     * @param int $qty
     * @return void
     */
    public function setProductQty($productName, $qty)
    {
        $productQtySelector = sprintf($this->cartItem, $productName) . $this->productQty;
        $this->_rootElement->find($productQtySelector, Locator::SELECTOR_XPATH)->setValue($qty);
    }

    /**
     * Get product quantity
     *
     * @param string $productName
     * @return string
     */
    public function getProductQty($productName)
    {
        $productQtySelector = sprintf($this->cartItem, $productName) . $this->productQty;
        return $this->_rootElement->find($productQtySelector, Locator::SELECTOR_XPATH)->getValue();
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
     * Get item Bundle options
     *
     * @param int $index
     * @param int $itemIndex
     * @param string $currency
     * @return string
     */
    public function getPriceBundleOptions($index, $itemIndex = 1, $currency = '$')
    {
        $formatPrice = sprintf($this->bundleOptions, $index, $itemIndex);
        return trim($this->_rootElement->find($formatPrice, Locator::SELECTOR_XPATH)->getText(), $currency);
    }
}
