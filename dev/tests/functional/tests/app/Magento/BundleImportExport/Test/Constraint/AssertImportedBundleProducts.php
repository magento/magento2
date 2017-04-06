<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\BundleImportExport\Test\Constraint;

use Magento\CatalogImportExport\Test\Constraint\AssertImportedProducts;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Assert that products data from CSV import file and data from product edit page are correct and match.
 */
class AssertImportedBundleProducts extends AssertImportedProducts
{
    /**
     * Product type.
     *
     * @var string
     */
    protected $productType = 'bundle';

    /**
     * Needed bundle product data.
     *
     * @var array
     */
    protected $neededKeys = [
        'sku',
        'name',
        'associated_skus',
        'bundle_values',
        'url_key',
    ];

    /**
     * Prepare bundle product data.
     *
     * @param FixtureInterface $product
     * @return array
     */
    protected function getDisplayedProductData(FixtureInterface $product)
    {
        $productData = $this->getDisplayedOnProductPageData($product);
        $bundleSelection = $productData['bundle_selections'][0];
        $assignedProduct = $bundleSelection['assigned_products'][0];

        $form = $this->catalogProductEdit->getProductForm();
        $form->openSection('bundle');
        $attributeSku = $form->getSection('bundle')->getAttributeSku();

        $productData['associated_skus'] = $attributeSku;
        $productType = ($productData['price_type'] === 'Yes')
            ? 'dynamic'
            : 'fixed';
        $productData['bundle_values'] = 'name=' .  $bundleSelection['title'] . ',type=select,required=1,sku='
            . $attributeSku . ',price=0.0000,default=0,default_qty='
            . $assignedProduct['selection_qty'] .'.0000,price_type=' . $productType;

        return $this->getResultProductsData($productData);
    }

    /**
     * Return string representation of object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Products data from CSV import file and data from product edit page are correct and match.';
    }
}
