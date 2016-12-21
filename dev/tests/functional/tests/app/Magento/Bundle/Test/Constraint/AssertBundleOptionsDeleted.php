<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Constraint;

use Magento\Bundle\Test\Fixture\BundleProduct;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Assert bundle product form.
 */
class AssertBundleOptionsDeleted extends AbstractConstraint
{
    /**
     * Assert that displayed price view for bundle product on product page equals passed from fixture.
     * @param BundleProduct $product
     * @param BundleProduct $originalProduct
     * @param CatalogProductIndex $productGrid
     * @param CatalogProductEdit $productPage
     * @return void
     */
    public function processAssert(
        BundleProduct $product,
        BundleProduct $originalProduct,
        CatalogProductIndex $productGrid,
        CatalogProductEdit $productPage
    ) {
        $filter = ['sku' => $product->getSku()];
        $productGrid->open();
        $productGrid->getProductGrid()->searchAndOpen($filter);

        $productData = $product->getData()['bundle_selections']['bundle_options'];
        $originalProductData = $originalProduct->getData()['bundle_selections']['bundle_options'];
        $formData = $productPage->getProductForm()->getData($product)['bundle_selections'];

        $productDataLength = count($productData);
        $formDataLength = count($productData);
        \PHPUnit_Framework_Assert::assertEquals($productDataLength, $formDataLength);

        foreach ($productData as $index => $option) {
            $productAssociatedDataLength = count($option['assigned_products']);
            $formAssociatedDataLength = count($formData[$index]['assigned_products']);
            \PHPUnit_Framework_Assert::assertEquals($productAssociatedDataLength, $formAssociatedDataLength);

            foreach ($option['assigned_products'] as $productIndex => $associatedProduct) {
                $associatedProduct['data']['getProductName'] =
                    $originalProductData[$index+1]['assigned_products'][$productIndex]['search_data']['name'];
                $associatedProduct = $associatedProduct['data'];
                $errorAssociatedProducts = array_diff(
                    $associatedProduct,
                    $formData[$index]['assigned_products'][$productIndex]
                );
                \PHPUnit_Framework_Assert::assertCount(0, $errorAssociatedProducts);
            }

            unset($option['assigned_products']);
            unset($formData[$index]['assigned_products']);
            $errorFields = array_diff($option, $formData[$index]);
            \PHPUnit_Framework_Assert::assertCount(0, $errorFields);
        }
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Bundle options were not deleted correctly. There is difference with expected options';
    }
}
