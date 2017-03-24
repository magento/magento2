<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\BundleImportExport\Test\Constraint;

use Magento\ImportExport\Test\Fixture\ImportData;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Catalog\Test\Constraint\AssertProductInGrid;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Mtf\Util\Protocol\CurlTransport\WebapiDecorator;
use Magento\CatalogImportExport\Test\Constraint\AssertImportProduct;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Assert products data from csv import file and page are match.
 */
class AssertBundleImportProduct extends AssertImportProduct
{
    /**
     * Product type.
     *
     * @var string
     */
    protected $productType = 'bundle';

    /**
     * Array keys mapping for csv file.
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
     * Assert imported products are correct.
     *
     * @param BrowserInterface $browser
     * @param CatalogProductIndex $catalogProductIndex
     * @param CatalogProductView $catalogProductView
     * @param AssertProductInGrid $assertProductInGrid
     * @param CatalogProductEdit $catalogProductEdit
     * @param WebapiDecorator $webApi
     * @param ImportData $import
     * @return void
     */
    public function processAssert(
        BrowserInterface $browser,
        CatalogProductIndex $catalogProductIndex,
        CatalogProductView $catalogProductView,
        AssertProductInGrid $assertProductInGrid,
        CatalogProductEdit $catalogProductEdit,
        WebapiDecorator $webApi,
        ImportData $import
    ) {
        parent::processAssert(
            $browser,
            $catalogProductIndex,
            $catalogProductView,
            $assertProductInGrid,
            $catalogProductEdit,
            $webApi,
            $import
        );
    }

    /**
     * Prepare bundle product data.
     *
     * @param FixtureInterface $product
     * @return array
     */
    protected function getPrepareProductsData(FixtureInterface $product)
    {
        $productSku = $product->getSku();
        $productId = $this->retrieveProductBySku($productSku)['id'];
        $this->catalogProductEdit->open(['id' => $productId]);
        $productData = $this->catalogProductEdit->getProductForm()->getData($product);

        $bundleSelection = $productData['bundle_selections'][0];
        $assignedProduct = $bundleSelection['assigned_products'][0];

        $form = $this->catalogProductEdit->getProductForm();
        $form->openSection('bundle');
        $container = $form->getSection('bundle');
        $attributeSku = $container->getAttributeSku();

        $productData['associated_skus'] = $attributeSku;
        $productData['bundle_values'] = 'name=' .  $bundleSelection['title'] . ',type=select,required=1,sku='
            . $attributeSku . ',price=0.0000,default=0,default_qty='
            . $assignedProduct['selection_qty'] .'.0000,price_type=fixed';

        return $productData;
    }

    /**
     * Return string representation of object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Imported bundle products are correct.';
    }
}
