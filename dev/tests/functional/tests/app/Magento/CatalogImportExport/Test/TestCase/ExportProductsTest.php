<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Test\TestCase;

use Magento\CatalogImportExport\Test\Constraint\AssertExportProductDate;
use Magento\CatalogImportExport\Test\Constraint\AssertExportProduct;
use Magento\ImportExport\Test\Page\Adminhtml\AdminExportIndex;
use Magento\ImportExport\Test\Fixture\ExportData;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Mtf\Util\Command\File\Export;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. Create products.
 *
 * Steps:
 * 1. Login to admin.
 * 2. Navigate to System > Export.
 * 3. Select Entity Type = Products.
 * 4. Fill Entity Attributes data.
 * 5. Click "Continue".
 * 6. Perform all assertions.
 *
 * @group ImportExport
 * @ZephyrId MAGETWO-46112
 */
class ExportProductsTest extends Injectable
{
    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Admin export index page.
     *
     * @var AdminExportIndex
     */
    private $adminExportIndex;

    /**
     * Assert export product.
     *
     * @var AssertExportProduct
     */
    private $assertExportProduct;

    /**
     * Assert export product date.
     *
     * @var AssertExportProductDate
     */
    private $assertExportProductDate;

    /**
     * Inject data.
     *
     * @param FixtureFactory $fixtureFactory
     * @param AdminExportIndex $adminExportIndex
     * @param AssertExportProduct $assertExportProduct
     * @param AssertExportProductDate $assertExportProductDate
     * @return void
     */
    public function __inject(
        FixtureFactory $fixtureFactory,
        AdminExportIndex $adminExportIndex,
        AssertExportProduct $assertExportProduct,
        AssertExportProductDate $assertExportProductDate
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $this->adminExportIndex = $adminExportIndex;
        $this->assertExportProduct = $assertExportProduct;
        $this->assertExportProductDate = $assertExportProductDate;
    }

    /**
     * Runs Export Product test.
     *
     * @param Export $export
     * @param string $exportData
     * @param array $exportedFields
     * @param array $products
     * @param array $advancedPricingAttributes
     * @param string|null $datePattern
     * @return void
     */
    public function test(
        Export $export,
        $exportData,
        array $exportedFields,
        array $products,
        array $advancedPricingAttributes = [],
        $datePattern = null
    ) {
        $products = $this->prepareProducts($products);
        $this->adminExportIndex->open();

        foreach ($products as $product) {
            if ($product->hasData('associated')) {
                $associatedProducts = $product->getAssociated()['products'];
                foreach ($associatedProducts as $associatedProduct) {
                    $this->adminExportIndex->getExportForm()->fill(
                        $this->prepareExportDataFixture($exportData, $associatedProduct),
                        null,
                        $advancedPricingAttributes
                    );
                    $this->adminExportIndex->getFilterExport()->clickContinue();
                    $this->assertExportProduct->processAssert($export, $exportedFields, $associatedProduct);
                }
            } else {
                $this->adminExportIndex->getExportForm()->fill(
                    $this->prepareExportDataFixture($exportData, $product),
                    null,
                    $advancedPricingAttributes
                );
                $this->adminExportIndex->getFilterExport()->clickContinue();
                $this->assertExportProduct->processAssert($export, $exportedFields, $product);
            }
            if ($datePattern) {
                $this->assertExportProductDate->processAssert($export, $datePattern);
            }
        }
    }

    /**
     * Prepare products for test.
     *
     * @param array $products
     * @return array|null
     */
    public function prepareProducts(array $products)
    {
        if (empty($products)) {
            return null;
        }
        $createdProducts = [];
        foreach ($products as $product) {
            $data = (isset($product['data'])) ? $product['data'] : [];
            if (isset($product['store'])) {
                $store = $this->fixtureFactory->createByCode('store', ['dataset' => $product['store']]);
                $store->persist();
                $data['website_ids'] = [['store' => $store]];
            }
            $product = $this->fixtureFactory->createByCode(
                $product['fixture'],
                [
                    'dataset' => $product['dataset'],
                    'data' => $data
                ]
            );
            $product->persist();
            $createdProducts[] = $product;
        }

        return $createdProducts;
    }

    /**
     * Prepare Export Data fixture.
     *
     * @param string $dataset
     * @param InjectableFixture|null $product
     * @return ExportData
     */
    private function prepareExportDataFixture($dataset, InjectableFixture $product = null)
    {
        $exportData = $this->fixtureFactory->createByCode(
            'exportData',
            [
                'dataset' => $dataset,
                'data' => [
                    'data_export' => $product
                ]
            ]
        );
        $exportData->persist();

        return $exportData;
    }
}
