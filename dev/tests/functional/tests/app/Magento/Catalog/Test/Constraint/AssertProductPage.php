<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProduct;
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
     * @var \Magento\ConfigurableProduct\Test\Block\Product\View
     */
    protected $productView;

    /**
     * Product fixture
     *
     * @var ConfigurableProduct
     */
    protected $product;

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
        $this->productView = $catalogProductView->getViewBlock();

        $errors = $this->verify();
        \PHPUnit_Framework_Assert::assertEmpty(
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
     * Verify displayed product name on product page(front-end) equals passed from fixture
     *
     * @return string|null
     */
    protected function verifyName()
    {
        $fixtureProductName = $this->product->getName();
        $formProductName = $this->productView->getProductName();

        if ($fixtureProductName == $formProductName) {
            return null;
        }
        return "Displayed product name on product page(front-end) not equals passed from fixture. "
        . "Actual: {$formProductName}, expected: {$fixtureProductName}.";
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
        $formPrice = $priceBlock->isOldPriceVisible() ? $priceBlock->getOldPrice() : $priceBlock->getPrice();
        $fixturePrice = number_format($this->product->getPrice(), 2, '.', '');

        if ($fixturePrice != $formPrice) {
            return "Displayed product price on product page(front-end) not equals passed from fixture. "
                . "Actual: {$fixturePrice}, expected: {$formPrice}.";
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
        $fixtureProductSpecialPrice = $this->product->getSpecialPrice();
        $fixtureProductSpecialPrice = number_format($fixtureProductSpecialPrice, 2);
        $formProductSpecialPrice = $this->productView->getPriceBlock()->getSpecialPrice();
        if ($fixtureProductSpecialPrice == $formProductSpecialPrice) {
            return null;
        }
        return "Displayed product special price on product page(front-end) not equals passed from fixture. "
            . "Actual: {$formProductSpecialPrice}, expected: {$fixtureProductSpecialPrice}.";
    }

    /**
     * Verify displayed product sku on product page(front-end) equals passed from fixture
     *
     * @return string|null
     */
    protected function verifySku()
    {
        $fixtureProductSku = $this->product->getSku();
        $formProductSku = $this->productView->getProductSku();

        if ($fixtureProductSku === null || $fixtureProductSku == $formProductSku) {
            return null;
        }
        return "Displayed product sku on product page(front-end) not equals passed from fixture. "
            . "Actual: {$formProductSku}, expected: {$fixtureProductSku}.";
    }

    /**
     * Verify displayed product description on product page(front-end) equals passed from fixture
     *
     * @return string|null
     */
    protected function verifyDescription()
    {
        $fixtureProductDescription = $this->product->getDescription();
        $formProductDescription = $this->productView->getProductDescription();

        if ($fixtureProductDescription == $formProductDescription) {
            return null;
        }
        return "Displayed product description on product page(front-end) not equals passed from fixture. "
            . "Actual: {$formProductDescription}, expected: {$fixtureProductDescription}.";
    }

    /**
     * Verify displayed product short description on product page(front-end) equals passed from fixture
     *
     * @return string|null
     */
    protected function verifyShortDescription()
    {
        $fixtureProductShortDescription = $this->product->getShortDescription();
        $formProductShortDescription = $this->productView->getProductShortDescription();

        if ($fixtureProductShortDescription == $formProductShortDescription) {
            return null;
        }
        return "Displayed product short description on product page(front-end) not equals passed from fixture. "
            . "Actual: {$formProductShortDescription}, expected: {$fixtureProductShortDescription}.";
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
