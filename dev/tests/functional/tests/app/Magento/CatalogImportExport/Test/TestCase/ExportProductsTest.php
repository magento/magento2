<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Test\TestCase;

use Magento\CatalogImportExport\Test\Constraint\AssertExportProduct;
use Magento\ImportExport\Test\Page\Adminhtml\AdminExportIndex;
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
 * @ZephyrId MAGETWO-46112, MAGETWO-46113, MAGETWO-46121, MAGETWO-30602, MAGETWO-46114, MAGETWO-46116, MAGETWO-46109
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
     * Inject data.
     *
     * @param FixtureFactory $fixtureFactory
     * @param AdminExportIndex $adminExportIndex
     * @param AssertExportProduct $assertExportProduct
     * @return void
     */
    public function __inject(
        FixtureFactory $fixtureFactory,
        AdminExportIndex $adminExportIndex,
        AssertExportProduct $assertExportProduct
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $this->adminExportIndex = $adminExportIndex;
        $this->assertExportProduct = $assertExportProduct;
    }

    /**
     * Runs Export Product test.
     *
     * @param Export $export
     * @param string $exportData
     * @param array $exportedFields
     * @param array $products
     * @return void
     */
    public function test(
        Export $export,
        $exportData,
        array $exportedFields,
        array $products
    ) {
        $products = $this->prepareProducts($products);
        $this->adminExportIndex->open();

        $exportData = $this->fixtureFactory->createByCode('exportData', ['dataset' => $exportData]);
        $exportData->persist();
        $this->adminExportIndex->getExportForm()->fill($exportData);
        $this->adminExportIndex->getFilterExport()->clickContinue();

        $this->assertExportProduct->processAssert($export, $exportedFields, $products);
    }

    /**
     * Prepare products for test.
     *
     * @param array $products
     * @return array|null
     */
    private function prepareProducts(array $products)
    {
        $createdProducts = [];
        foreach ($products as $product) {
            $data = isset($product['data']) ? $product['data'] : [];
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
}
