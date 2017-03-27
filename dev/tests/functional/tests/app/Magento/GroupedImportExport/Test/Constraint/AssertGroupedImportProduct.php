<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedImportExport\Test\Constraint;

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
class AssertGroupedImportProduct extends AssertImportProduct
{
    /**
     * Product type.
     *
     * @var string
     */
    protected $productType = 'grouped';
    /**
     * Needed grouped product data.
     *
     * @var array
     */
    protected $neededKeys = [
        'sku',
        'name',
        'associated_skus',
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
     * Prepare grouped product data.
     *
     * @param FixtureInterface $product
     * @return array
     */
    protected function getPrepareProductsData(FixtureInterface $product)
    {
        $productId = $this->retrieveProductBySku($product)['id'];
        $this->catalogProductEdit->open(['id' => $productId]);
        $productData = $this->catalogProductEdit->getProductForm()->getData($product);
        $assignedProduct = $productData['associated']['assigned_products'][0];
        $form = $this->catalogProductEdit->getProductForm();
        $form->openSection('grouped');
        $assignedProductSku = $form->getSection('grouped')->getListAssociatedProductsBlock()->getAssociatedProductSku();
        $productData['associated_skus'] = $assignedProductSku . '=' . $assignedProduct['qty'];
        unset($productData['associated']);

        return $productData;
    }

    /**
     * Return string representation of object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Imported grouped products are correct.';
    }
}
