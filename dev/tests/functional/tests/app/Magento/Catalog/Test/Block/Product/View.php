<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Product;

use Magento\Catalog\Test\Block\AbstractConfigureBlock;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Fixture\InjectableFixture;

/**
 * Product view block on the product page.
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 */
class View extends AbstractConfigureBlock
{
    /**
     * XPath selector for tab.
     *
     * @var string
     */
    protected $tabSelector = './/div[@data-role="collapsible" and a[contains(text(),"%s")]]';

    /**
     * Custom options CSS selector.
     *
     * @var string
     */
    protected $customOptionsSelector = '.product-options-wrapper';

    /**
     * 'Add to Cart' button.
     *
     * @var string
     */
    protected $addToCart = '.tocart';

    /**
     * Quantity input id.
     *
     * @var string
     */
    protected $qty = '#qty';

    /**
     * Add to cart form id.
     *
     * @var string
     */
    protected $addToCartForm = '#product_addtocart_form';

    /**
     * 'Check out with PayPal' button.
     *
     * @var string
     */
    protected $paypalCheckout = '[data-action=checkout-form-submit]';

    /**
     * 'Check out with PayPal' button.
     *
     * @var string
     */
    protected $inContextPaypalCheckout = '#paypal-express-in-context-mini-cart';

    /**
     * Product name element.
     *
     * @var string
     */
    protected $productName = '.page-title-wrapper.product h1.page-title .base';

    /**
     * Product sku element.
     *
     * @var string
     */
    protected $productSku = '[itemprop="sku"]';

    /**
     * Product description element.
     *
     * @var string
     */
    protected $productDescription = '.product.attribute.description';

    /**
     * Product short-description element.
     *
     * @var string
     */
    protected $productShortDescription = '.product.attribute.overview';

    /**
     * Stock Availability control.
     *
     * @var string
     */
    protected $stockAvailability = '.stock span';

    /**
     * This member holds the class name of the tier price block.
     *
     * @var string
     */
    protected $tierPricesSelector = "//ul[contains(@class,'tier')]//*[@class='item'][%line-number%]";

    /**
     * Selector for price block.
     *
     * @var string
     */
    protected $priceBlock = '//*[@class="product-info-main"]//*[contains(@class,"price-box")]';

    /**
     * 'Add to Compare' button.
     *
     * @var string
     */
    protected $clickAddToCompare = '.action.tocompare';

    /**
     * Locator value for "Add to Wish List" button.
     *
     * @var string
     */
    protected $addToWishlist = '[data-action="add-to-wishlist"]';

    /**
     * Messages block locator.
     *
     * @var string
     */
    protected $messageBlock = '.page.messages';

    /**
     * Minicart block locator.
     *
     * @var string
     */
    protected $miniCartBlock = '[data-block="minicart"]';

    /**
     * Success message selector.
     *
     * @var string
     */
    protected $successMessage = '[data-ui-id$=message-success]';

    /**
     * Product media gallery selector.
     *
     * @var string
     */
    protected $mediaGallery = '[data-gallery-role="gallery"] img';

    /**
     * Locator for page with ajax loading state.
     *
     * @var string
     */
    protected $ajaxLoading = 'body.ajax-loading';

    /**
     * Full image selector
     *
     * @var string
     */
    protected $fullImage = '[data-gallery-role="gallery"] img.fotorama__img--full';

    /**
     * Full image close selector
     *
     * @var string
     */
    protected $fullImageClose = '[data-gallery-role="fotorama__fullscreen-icon"]';

    /**
     * Base image selector
     *
     * @var string
     */
    protected $baseImage = '[data-gallery-role="gallery"] img.fotorama__img.fotorama__img';

    /**
     * @var string
     */
    protected $galleryLoader = '.fotorama__spinner--show';

    /**
     * Video Container selector
     *
     * @var string
     */
    private $videoContainer = 'div.fotorama-video-container';

    /**
     * Success message block after add to cart click.
     *
     * @var string
     */
    private $addToCartSuccess = '.message-success';

    /**
     * Get block price.
     *
     * @param FixtureInterface|null $product
     *
     * @return Price
     */
    public function getPriceBlock(FixtureInterface $product = null)
    {
        $typeId = null;

        if ($product) {
            $dataConfig = $product->getDataConfig();
            $typeId = isset($dataConfig['type_id']) ? $dataConfig['type_id'] : null;
        }

        if ($this->hasRender($typeId)) {
            return $this->callRender($typeId, 'getPriceBlock');
        }

        return $this->blockFactory->create(
            'Magento\Catalog\Test\Block\Product\Price',
            ['element' => $this->_rootElement->find($this->priceBlock, Locator::SELECTOR_XPATH)]
        );
    }

    /**
     * Add product to shopping cart.
     *
     * @param FixtureInterface $product
     * @return void
     */
    public function addToCart(FixtureInterface $product)
    {
        /** @var \Magento\Checkout\Test\Block\Cart\Sidebar $miniCart */
        $miniCart = $this->blockFactory->create(
            '\Magento\Checkout\Test\Block\Cart\Sidebar',
            ['element' => $this->browser->find($this->miniCartBlock)]
        );
        /** @var CatalogProductSimple $product */
        $checkoutData = $product->getCheckoutData();

        $miniCart->waitInit();
        $this->fillOptions($product);
        if (isset($checkoutData['qty'])) {
            $this->setQty($checkoutData['qty']);
        }
        $this->clickAddToCart();
        $miniCart->waitLoader();
    }

    /**
     * Click link.
     *
     * @return void
     */
    public function clickAddToCart()
    {
        $this->_rootElement->find($this->addToCart, Locator::SELECTOR_CSS)->click();
        $this->waitForElementVisible($this->addToCartSuccess);
    }

    /**
     * Set quantity and click add to cart.
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
     * Set quantity.
     *
     * @param int $qty
     * @return void
     */
    public function setQty($qty)
    {
        $this->_rootElement->find($this->qty)->setValue($qty);
        $this->_rootElement->find($this->addToCartForm)->click();
    }

    /**
     * Find Add To Cart button.
     *
     * @return bool
     */
    public function isVisibleAddToCart()
    {
        return $this->_rootElement->find($this->addToCart, Locator::SELECTOR_CSS)->isVisible();
    }

    /**
     * Press 'Check out with PayPal' button.
     *
     * @return void
     */
    public function paypalCheckout()
    {
        $this->_rootElement->find($this->paypalCheckout, Locator::SELECTOR_CSS)->click();
        $this->waitForElementNotVisible($this->paypalCheckout);
    }

    /**
     * Press 'Check out with PayPal' button.
     *
     * @return void
     */
    public function inContextPaypalCheckout()
    {
        $this->_rootElement->find($this->inContextPaypalCheckout, Locator::SELECTOR_CSS)->click();
        $this->waitForElementNotVisible($this->inContextPaypalCheckout);
    }

    /**
     * Press 'Check out with Braintree PayPal' button.
     * 
     * @return string
     */
    public function braintreePaypalCheckout()
    {
        $currentWindow = $this->browser->getCurrentWindow();
        /** @var \Magento\Checkout\Test\Block\Cart\Sidebar $miniCart */
        $miniCart = $this->blockFactory->create(
            '\Magento\Checkout\Test\Block\Cart\Sidebar',
            ['element' => $this->browser->find($this->miniCartBlock)]
        );

        $miniCart->openMiniCart();
        $miniCart->clickBraintreePaypalButton();
        return $currentWindow;
    }

    /**
     * Get product name displayed on page.
     *
     * @return string
     */
    public function getProductName()
    {
        return $this->_rootElement->find($this->productName, Locator::SELECTOR_CSS)->getText();
    }

    /**
     * Get product sku displayed on page.
     *
     * @return string
     */
    public function getProductSku()
    {
        return $this->_rootElement->find($this->productSku, Locator::SELECTOR_CSS)->getText();
    }

    /**
     * Return product short description on page.
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
     * Return product description on page.
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
     * Return product options.
     *
     * @param FixtureInterface $product
     * @return array
     */
    public function getOptions(FixtureInterface $product)
    {
        /** @var CatalogProductSimple $product */
        $dataConfig = $product->getDataConfig();
        $typeId = isset($dataConfig['type_id']) ? $dataConfig['type_id'] : null;

        return $this->hasRender($typeId) ? $this->callRender(
            $typeId,
            'getOptions',
            ['product' => $product]
        ) : $this->getCustomOptionsBlock()->getOptions($product);
    }

    /**
     * This method return array tier prices.
     *
     * @param int $lineNumber [optional]
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
     * Click "ADD TO CART" button.
     *
     * @return void
     */
    public function clickAddToCartButton()
    {
        $this->_rootElement->find($this->addToCart, Locator::SELECTOR_CSS)->click();
    }

    /**
     * Check 'Add to card' button visible.
     *
     * @return bool
     */
    public function isVisibleAddToCardButton()
    {
        return $this->_rootElement->find($this->addToCart, Locator::SELECTOR_CSS)->isVisible();
    }

    /**
     * Get text of Stock Availability control.
     *
     * @return string
     */
    public function stockAvailability()
    {
        return strtolower($this->_rootElement->find($this->stockAvailability)->getText());
    }

    /**
     * Click "Add to Compare" button.
     *
     * @return void
     */
    public function clickAddToCompare()
    {
        /** @var \Magento\Backend\Test\Block\Messages $messageBlock */
        $messageBlock = $this->blockFactory->create(
            'Magento\Backend\Test\Block\Messages',
            ['element' => $this->browser->find($this->messageBlock)]
        );
        $this->_rootElement->find($this->clickAddToCompare, Locator::SELECTOR_CSS)->click();
        $messageBlock->waitSuccessMessage();
    }

    /**
     * Add product to Wishlist.
     *
     * @param FixtureInterface $product
     * @return void
     */
    public function addToWishlist(FixtureInterface $product)
    {
        /** @var CatalogProductSimple $product */
        $checkoutData = $product->getCheckoutData();
        $this->fillOptions($product);
        if (isset($checkoutData['qty'])) {
            $this->setQty($checkoutData['qty']);
        }
        $this->clickAddToWishlist();
    }

    /**
     * Click "Add to Wish List".
     *
     * @return void
     */
    public function clickAddToWishlist()
    {
        $this->_rootElement->find($this->addToWishlist)->click();
    }

    /**
     * Select tab on the product page.
     *
     * @param string $name
     * @return void
     */
    public function selectTab($name)
    {
        $this->_rootElement->find(sprintf($this->tabSelector, $name), Locator::SELECTOR_XPATH)->click();
    }

    /**
     * Wait loading block.
     *
     * @return void
     */
    public function waitLoader()
    {
        $this->waitForElementNotVisible($this->ajaxLoading);
    }

    /**
     * Check id media gallery is visible for the product.
     *
     * @return bool
     */
    public function isGalleryVisible()
    {
        $this->waitForElementNotVisible($this->galleryLoader);
        return $this->_rootElement->find($this->mediaGallery)->isVisible();
    }

    /**
     * Check is full image into gallery is visible for the product.
     *
     * @return bool
     */
    public function isFullImageVisible()
    {
        $this->waitForElementNotVisible($this->galleryLoader);
        return $this->browser->find($this->fullImage)->isVisible();
    }

    /**
     * Get full image source from media gallery into product
     *
     * @return string
     */
    public function getFullImageSource()
    {
        return $this->browser->find($this->fullImage)->getAttribute('src');
    }

    /**
     * Check is base image into gallery is visible for the product.
     *
     * @return bool
     */
    public function isBaseImageVisible()
    {
        return $this->_rootElement->find($this->baseImage)->isVisible();
    }

    /**
     * Get full image source from media gallery into product
     *
     * @return string
     */
    public function getBaseImageSource()
    {
        return $this->_rootElement->find($this->baseImage)->getAttribute('src');
    }

    /**
     * Click link.
     *
     * @return void
     */
    public function clickBaseImage()
    {
        $this->_rootElement->find($this->baseImage, Locator::SELECTOR_CSS)->click();
        $this->waitForElementVisible($this->fullImage);
    }

    /**
     * Click link.
     *
     * @return void
     */
    public function closeFullImage()
    {
        $this->_rootElement->waitUntil(
            function () {
                $this->browser->find($this->fullImage)->hover();

                if ($this->browser->find($this->fullImageClose)->isVisible()) {
                    $this->browser->find($this->fullImageClose)->click();

                    return true;
                }

                return null;
            }
        );
    }

    /**
     * Check is video is visible on product page
     *
     * @return bool
     */
    public function isVideoVisible()
    {
        return $this->_rootElement->find($this->videoContainer)->isVisible();
    }
}
