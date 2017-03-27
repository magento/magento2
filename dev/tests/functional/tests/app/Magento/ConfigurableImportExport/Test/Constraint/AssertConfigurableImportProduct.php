<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableImportExport\Test\Constraint;

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
class AssertConfigurableImportProduct extends AssertImportProduct
{
    /**
     * Product type.
     *
     * @var string
     */
    protected $productType = 'configurable';

    /**
     * Needed configurable product data.
     *
     * @var array
     */
    protected $neededKeys = [
        'sku',
        'name',
        'additional_attributes',
        'configurable_variations',
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
     * Prepare configurable product data.
     *
     * @param FixtureInterface $product
     * @return array
     */
    protected function getPrepareProductsData(FixtureInterface $product)
    {
        $productId = $this->retrieveProductBySku($product)['id'];
        $this->catalogProductEdit->open(['id' => $productId]);
        $productData = $this->catalogProductEdit->getProductForm()->getData($product);
        $attributesData = $productData['configurable_attributes_data']['matrix']['0'];
        $form = $this->catalogProductEdit->getProductForm();
        $form->openSection('variations');
        $productAttribute = $form->getSection('variations')->getVariationsBlock()->getProductAttribute();
        $productAttribute = str_replace(': ', '=', $productAttribute);
        $productData['additional_attributes'] = $productAttribute;
        $productData['configurable_variations'] = 'sku=' . $attributesData['sku'] . ',' . $productAttribute;
        unset($productData['configurable_attributes_data']);

        return $this->getResultProductsData($productData);
    }

    /**
     * Return string representation of object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Imported configurable products are correct.';
    }
}
