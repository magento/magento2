<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Swatches\Test\Constraint;

use Magento\Catalog\Test\Constraint\AssertProductPage;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Mtf\Client\BrowserInterface;

/**
 * Assert that product with swatches and regular dropdown redirect can't be add to cart from catalog catergory page.
 */
class AssertSwatchConfigurableProductPage extends AssertProductPage
{
    /**
     * {@inheritdoc}
     */
    public function processAssert(
        BrowserInterface $browser,
        CatalogProductView $catalogProductView,
        FixtureInterface $product
    ) {
        $this->product = $product;
        $this->productView = $catalogProductView->getProductViewWithSwatchesBlock();
        $this->objectManager->create(
            \Magento\Swatches\Test\TestStep\AddProductToCartFromCatalogCategoryPageStep::class,
            [
                'product' => $product
            ]
        )->run();
        // we need this line for waiti until page will be fully loaded
        $this->productView->getSelectedSwatchOptions($this->product);
        $errors = $this->verify();
        \PHPUnit_Framework_Assert::assertEmpty(
            $errors,
            "\nFound the following errors:\n" . implode(" \n", $errors)
        );
    }

    /**
     * Verify product on product view page.
     *
     * @return array
     */
    protected function verify()
    {
        $errors = parent::verify();
        $errors[] = $this->verifySwatches();

        return array_filter($errors);
    }

    /**
     * Verify selected swatches on product view page.
     *
     * @return array
     */
    protected function verifySwatches()
    {
        $actualData = $this->productView->getSelectedSwatchOptions($this->product);
        $expectedData = $this->convertCheckoutData($this->product);
        $this->verifyData($expectedData, $actualData);
    }

    /**
     * Get swatch attributes formatter to attributes comparison.
     *
     * @param FixtureInterface $product
     * @return array
     */
    public function convertCheckoutData(FixtureInterface  $product)
    {
        $out = [];
        $checkoutData = $product->getCheckoutData();
        $availableAttributes = $product->getConfigurableAttributesData();
        $attributesData = $availableAttributes['attributes_data'];
        foreach ($checkoutData['options']['configurable_options'] as $item) {
            $out[$item['title']] = $attributesData[$item['title']]['options'][$item['value']]['label'];
        }

        return $out;
    }

    /**
     * Return string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Swatch attributes displayed as expected on product page';
    }
}
