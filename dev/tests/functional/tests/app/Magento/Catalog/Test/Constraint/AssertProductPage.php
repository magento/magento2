<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractAssertForm;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Class AssertProductPage
 * Assert that displayed product data on product page(front-end) equals passed from fixture.
 */
class AssertProductPage extends AbstractAssertForm
{
    /**
     * Product view block on frontend page
     *
     * @var \Magento\Catalog\Test\Block\Product\View
     */
    protected $productView;

    /**
     * Product fixture
     *
     * @var FixtureInterface
     */
    protected $product;

    /**
     * @var CatalogProductView
     */
    protected $pageView;

    /**
     * Assert that displayed product data on product page(front-end) equals passed from fixture:
     * 1. Product Name
     * 2. Price
     * 3. Special price
     * 4. SKU
     * 5. Description
     * 6. Short Description
     *
     * @param BrowserInterface $browser
     * @param CatalogProductView $catalogProductView
     * @param FixtureInterface $product
     * @return void
     */
    public function processAssert(
        BrowserInterface $browser,
        CatalogProductView $catalogProductView,
        FixtureInterface $product
    ) {
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');

        $this->product = $product;
        $this->pageView = $catalogProductView;
        $this->productView = $catalogProductView->getViewBlock();

        $errors = $this->verify();
        \PHPUnit\Framework\Assert::assertEmpty(
            $errors,
            "\nFound the following errors:\n" . implode(" \n", $errors)
        );
    }

    /**
     * Verify displayed product data on product page(front-end) equals passed from fixture
     *
     * @return array
     */
    protected function verify()
    {
        $errors = [];

        $errors[] = $this->verifyName();
        $errors[] = $this->verifyPrice();
        $errors[] = $this->verifySpecialPrice();
        $errors[] = $this->verifySku();
        $errors[] = $this->verifyDescription();
        $errors[] = $this->verifyShortDescription();

        return array_filter($errors);
    }

    /**
     * Verify displayed product name on Storefront product page equals to the passed from fixture
     *
     * @return string|null
     */
    protected function verifyName()
    {
        $expectedName = $this->product->getName();
        try {
            $actualName = $this->productView->getProductName();
        } catch (\PHPUnit_Extensions_Selenium2TestCase_WebDriverException $e) {
            return "Could not find product '{$this->product->getName()}' name on the page.\n" . $e->getMessage();
        }

        if ($expectedName == $actualName) {
            return null;
        }
        return "Product name on Storefront product '{$this->product->getName()}' page is unexpected. "
        . "Actual: {$actualName}, expected: {$expectedName}.";
    }

    /**
     * Verify displayed product price on product page(front-end) equals passed from fixture
     *
     * @return string|null
     */
    protected function verifyPrice()
    {
        if ($this->product->hasData('price') == false) {
            return null;
        }

        $priceBlock = $this->productView->getPriceBlock();
        if (!$priceBlock->isVisible()) {
            return "Price block for '{$this->product->getName()}' product' is not visible.";
        }
        $actualPrice = $priceBlock->isOldPriceVisible() ? $priceBlock->getOldPrice() : $priceBlock->getPrice();
        $expectedPrice = number_format($this->product->getPrice(), 2, '.', '');

        if ($expectedPrice != $actualPrice) {
            return "Displayed product price on Storefront product '{$this->product->getName()}' page is unexpected. "
                . "Actual: {$actualPrice}, expected: {$expectedPrice}.";
        }
        return null;
    }

    /**
     * Verify displayed product special price on product page(front-end) equals passed from fixture
     *
     * @return string|null
     */
    protected function verifySpecialPrice()
    {
        if (!$this->product->hasData('special_price')) {
            return null;
        }
        $expectedSpecialPrice = $this->product->getSpecialPrice();
        $expectedSpecialPrice = number_format($expectedSpecialPrice, 2);
        $priceBlock = $this->productView->getPriceBlock($this->product);
        if (!$priceBlock->isVisible()) {
            return "Price block for '{$this->product->getName()}' product' is not visible.";
        }
        $actualSpecialPrice = $priceBlock->getSpecialPrice();
        if ($expectedSpecialPrice == $actualSpecialPrice) {
            return null;
        }
        return "Displayed product special price on Storefront product '{$this->product->getName()}' page is unexpected."
            . "Actual: {$actualSpecialPrice}, expected: {$expectedSpecialPrice}.";
    }

    /**
     * Verify displayed product sku on product page(front-end) equals passed from fixture
     *
     * @return string|null
     */
    protected function verifySku()
    {
        $expectedSku = $this->product->getSku();
        try {
            $actualSku = $this->productView->getProductSku();
        } catch (\PHPUnit_Extensions_Selenium2TestCase_WebDriverException $e) {
            return "Could not find product {$this->product->getName()}' SKU on the page.\n" . $e->getMessage();
        }

        if ($expectedSku === null || $expectedSku == $actualSku) {
            return null;
        }
        return "Displayed product SKU on Storefront product '{$this->product->getName()}' page is unexpected. "
            . "Actual: {$actualSku}, expected: {$expectedSku}.";
    }

    /**
     * Verify displayed product description on product page(front-end) equals passed from fixture
     *
     * @return string|null
     */
    protected function verifyDescription()
    {
        $expectedDescription = $this->product->getDescription();
        $actualDescription = $this->productView->getProductDescription();

        if ($expectedDescription === null || $expectedDescription == $actualDescription) {
            return null;
        }
        return "Displayed product description on Storefront product '{$this->product->getName()}' page is unexpected. "
            . "Actual: {$actualDescription}, expected: {$expectedDescription}.";
    }

    /**
     * Verify displayed product short description on product page(front-end) equals passed from fixture
     *
     * @return string|null
     */
    protected function verifyShortDescription()
    {
        $expected = $this->product->getShortDescription();
        $actual = $this->productView->getProductShortDescription();

        if ($expected === null || $expected == $actual) {
            return null;
        }
        return "Displayed short description on Storefront product '{$this->product->getName()}' page is unexpected. "
            . "Actual: {$actual}, expected: {$expected}.";
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Product on product view page is correct.';
    }
}
