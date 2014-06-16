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

namespace Magento\Catalog\Test\Block\Product;

use Mtf\Block\Block;
use Mtf\Factory\Factory;
use Mtf\Client\Element\Locator;
use Mtf\Fixture\FixtureInterface;
use Magento\Catalog\Test\Fixture\GroupedProduct;
use Magento\Catalog\Test\Fixture\ConfigurableProduct;

/**
 * Class View
 * Product view block on the product page
 */
class View extends Block
{
    /**
     * 'Add to Cart' button
     *
     * @var string
     */
    protected $addToCart = '#product-addtocart-button';

    /**
     * Quantity input id
     *
     * @var string
     */
    protected $qty = '#qty';

    /**
     * 'Check out with PayPal' button
     *
     * @var string
     */
    protected $paypalCheckout = '[data-action=checkout-form-submit]';

    /**
     * This member holds the class name for the price block found inside the product details.
     *
     * @var string
     */
    protected $priceBlockClass = 'price-box';

    /**
     * Product name element
     *
     * @var string
     */
    protected $productName = '.page.title.product span';

    /**
     * Product sku element
     *
     * @var string
     */
    protected $productSku = '[itemprop="sku"]';

    /**
     * Product description element
     *
     * @var string
     */
    protected $productDescription = '.product.attibute.description';

    /**
     * Product short-description element
     *
     * @var string
     */
    protected $productShortDescription = '.product.attibute.overview';

    /**
     * Product price element
     *
     * @var string
     */
    protected $productPrice = '.price-box .price';

    /**
     * Bundle options block
     *
     * @var string
     */
    protected $bundleBlock = '#product-options-wrapper';

    /**
     * Click for Price link on Product page
     *
     * @var string
     */
    protected $clickForPrice = '[id*=msrp-popup]';

    /**
     * MAP popup on Product page
     *
     * @var string
     */
    protected $mapPopup = '#map-popup-click-for-price';

    /**
     * Stock Availability control
     *
     * @var string
     */
    protected $stockAvailability = '.stock span';

    /**
     * Customize and add to cart button selector
     *
     * @var string
     */
    protected $customizeButton = '.action.primary.customize';

    /**
     * This member holds the class name of the tier price block.
     *
     * @var string
     */
    protected $tierPricesSelector = "//ul[contains(@class,'tier')]//*[@class='item'][%line-number%]";

    /**
     * Get bundle options block
     *
     * @return \Magento\Bundle\Test\Block\Catalog\Product\View\Type\Bundle
     */
    public function getBundleBlock()
    {
        return Factory::getBlockFactory()->getMagentoBundleCatalogProductViewTypeBundle(
            $this->_rootElement->find($this->bundleBlock)
        );
    }

    /**
     * Get block price
     *
     * @return \Magento\Catalog\Test\Block\Product\Price
     */
    protected function getPriceBlock()
    {
        return Factory::getBlockFactory()->getMagentoCatalogProductPrice(
            $this->_rootElement->find('.product.info.main .price-box')
        );
    }

    /**
     * Add product to shopping cart
     *
     * @param FixtureInterface $product
     * @return void
     */
    public function addToCart(FixtureInterface $product)
    {
        $this->fillOptions($product);
        $this->clickAddToCart();
    }

    /**
     * Find button 'Add to cart'
     *
     * @return boolean
     */
    public function addToCartIsVisible()
    {
        return $this->_rootElement->find($this->addToCart, Locator::SELECTOR_CSS)->isVisible();
    }

    /**
     * Click link
     *
     * @return void
     */
    public function clickAddToCart()
    {
        $this->_rootElement->find($this->addToCart, Locator::SELECTOR_CSS)->click();
    }

    /**
     * Set quantity and click add to cart
     *
     * @param int $qty
     * @return void
     */
    public function setQtyAndClickAddToCart($qty)
    {
        $this->_rootElement->find($this->qty, Locator::SELECTOR_CSS)->setValue($qty);
        $this->clickAddToCart();
    }

    /**
     * Find Add To Cart button
     *
     * @return bool
     */
    public function isVisibleAddToCart()
    {
        return $this->_rootElement->find($this->addToCart, Locator::SELECTOR_CSS)->isVisible();
    }

    /**
     * Press 'Check out with PayPal' button
     *
     * @return void
     */
    public function paypalCheckout()
    {
        $this->_rootElement->find($this->paypalCheckout, Locator::SELECTOR_CSS)->click();
    }

    /**
     * Get product name displayed on page
     *
     * @return string
     */
    public function getProductName()
    {
        return $this->_rootElement->find($this->productName, Locator::SELECTOR_CSS)->getText();
    }

    /**
     * Get product sku displayed on page
     *
     * @return string
     */
    public function getProductSku()
    {
        return $this->_rootElement->find($this->productSku, Locator::SELECTOR_CSS)->getText();
    }

    /**
     * This method returns the price box block.
     *
     * @return Price
     */
    public function getProductPriceBlock()
    {
        return $this->blockFactory->create(
            'Magento\Catalog\Test\Block\Product\Price',
            ['element' => $this->_rootElement->find($this->priceBlockClass, Locator::SELECTOR_CLASS_NAME)]
        );
    }

    /**
     * Return product price displayed on page
     *
     * @return array|string Returns arrays with keys corresponding to fixture keys
     */
    public function getProductPrice()
    {
        return $this->getPriceBlock()->getPrice();
    }

    /**
     * Return product short description on page
     *
     * @return string|null
     */
    public function getProductShortDescription()
    {
        if ($this->_rootElement->find($this->productShortDescription, Locator::SELECTOR_CSS)->isVisible()) {
            return $this->_rootElement->find($this->productShortDescription, Locator::SELECTOR_CSS)->getText();
        }
        return null;
    }

    /**
     * Return product description on page
     *
     * @return string|null
     */
    public function getProductDescription()
    {
        if ($this->_rootElement->find($this->productDescription, Locator::SELECTOR_CSS)->isVisible()) {
            return $this->_rootElement->find($this->productDescription, Locator::SELECTOR_CSS)->getText();
        }
        return null;
    }

    /**
     * Return configurable product options
     *
     * @return array
     */
    public function getProductOptions()
    {
        $options = array();
        for ($i = 2; $i <= 3; $i++) {
            $options[] = $this->_rootElement->find(".super-attribute-select option:nth-child({$i})")->getText();
        }
        return $options;
    }

    /**
     * Verify configurable product options
     *
     * @param ConfigurableProduct $product
     * @return bool
     */
    public function verifyProductOptions(ConfigurableProduct $product)
    {
        $attributes = $product->getConfigurableOptions();
        foreach ($attributes as $attributeName => $attribute) {
            foreach ($attribute as $optionName) {
                $option = $this->_rootElement->find(
                    '//*[*[@class="field configurable required"]//span[text()="' .
                    $attributeName .
                    '"]]//select/option[contains(text(), "' .
                    $optionName .
                    '")]',
                    Locator::SELECTOR_XPATH
                );
                if (!$option->isVisible()) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Fill in the option specified for the product
     *
     * @param FixtureInterface $product
     * @return void
     */
    public function fillOptions(FixtureInterface $product)
    {
        $configureButton = $this->_rootElement->find($this->customizeButton);
        $configureSection = $this->_rootElement->find('.product.options.wrapper');

        if ($configureButton->isVisible()) {
            $configureButton->click();
            $bundleOptions = $product->getSelectionData();
            $this->getBundleBlock()->fillBundleOptions($bundleOptions);
        }
        if ($configureSection->isVisible()) {
            $productOptions = $product->getProductOptions();
            $this->getBundleBlock()->fillProductOptions($productOptions);
        }
    }

    /**
     * This method return array tier prices
     *
     * @param int $lineNumber
     * @return array
     */
    public function getTierPrices($lineNumber = 1)
    {
        return $this->_rootElement->find(
            str_replace('%line-number%', $lineNumber, $this->tierPricesSelector),
            Locator::SELECTOR_XPATH
        )->getText();
    }

    /**
     * Click "Customize and add to cart button"
     *
     * @return void
     */
    public function clickCustomize()
    {
        $this->_rootElement->find($this->customizeButton)->click();

    }

    /**
     * Click "ADD TO CART" button
     *
     * @return void
     */
    public function clickAddToCartButton()
    {
        $this->_rootElement->find($this->addToCart, Locator::SELECTOR_CSS)->click();
    }

    /**
     * Verification of group products
     *
     * @param GroupedProduct $product
     * @return bool
     */
    public function verifyGroupedProducts(GroupedProduct $product)
    {
        foreach ($product->getAssociatedProductNames() as $name) {
            $option = $this->_rootElement->find(
                "//*[@id='super-product-table']//tr[td/strong='{$name}']",
                Locator::SELECTOR_XPATH
            );
            if (!$option->isVisible()) {
                return false;
            }
        }
        return true;
    }

    /**
     * Open MAP block on Product View page
     *
     * @return void
     */
    public function openMapBlockOnProductPage()
    {
        $this->_rootElement->find($this->clickForPrice, Locator::SELECTOR_CSS)->click();
        $this->waitForElementVisible($this->mapPopup, Locator::SELECTOR_CSS);
    }

    /**
     * Check 'Add to card' button visible
     *
     * @return bool
     */
    public function checkAddToCardButton()
    {
        return $this->_rootElement->find($this->addToCart, Locator::SELECTOR_CSS)->isVisible();
    }

    /**
     * Get text of Stock Availability control
     *
     * @return string
     */
    public function stockAvailability()
    {
        return $this->_rootElement->find($this->stockAvailability)->getText();
    }
}
