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
     * Array keys mapping for csv file.
     *
     * @var array
     */
    protected $mappingKeys = [
        'sku' => 'sku',
        'name' => 'name',
        'additional_attributes' => 'additional_attributes',
        'configurable_variations' => 'configurable_variations',
        'url_key' => 'url_key',
    ];

    /**
     * Attribute selector.
     *
     * @var string
     */
    private $attribute = 'div[data-index="attributes"] span[data-index="attributes"';

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
     * @param string $productType
     * @return void
     */
    public function processAssert(
        BrowserInterface $browser,
        CatalogProductIndex $catalogProductIndex,
        CatalogProductView $catalogProductView,
        AssertProductInGrid $assertProductInGrid,
        CatalogProductEdit $catalogProductEdit,
        WebapiDecorator $webApi,
        ImportData $import,
        $productType = 'configurable'
    ) {
        parent::processAssert(
            $browser,
            $catalogProductIndex,
            $catalogProductView,
            $assertProductInGrid,
            $catalogProductEdit,
            $webApi,
            $import,
            $productType
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
        $productSku = $product->getSku();
        $productId = $this->retrieveProductBySku($productSku)['id'];
        $this->catalogProductEdit->open(['id' => $productId]);
        $productData = $this->catalogProductEdit->getProductForm()->getData($product);
        $attributesData = $productData['configurable_attributes_data']['matrix']['0'];
        $attribute = str_replace(': ', '=', $this->browser->find($this->attribute)->getText());
        $productData['additional_attributes'] = str_replace(': ', '=', $attribute);
        $productData['configurable_variations'] = 'sku=' . $attributesData['sku'] . ',' . $attribute;
        unset($productData['configurable_attributes_data']);

        return $productData;
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
