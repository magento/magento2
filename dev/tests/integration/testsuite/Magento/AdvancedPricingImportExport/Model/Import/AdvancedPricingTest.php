<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedPricingImportExport\Model\Import;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\ImportExport\Model\Import;

/**
 * @magentoAppArea adminhtml
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AdvancedPricingTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product
     */
    protected $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $fileSystem;

    /**
     * Expected Product Tier Price mapping with data
     *
     * @var array
     */
    protected $expectedTierPrice;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->fileSystem = $this->objectManager->get(\Magento\Framework\Filesystem::class);
        $this->model = $this->objectManager->create(
            \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::class
        );
        $this->expectedTierPrice = [
            'AdvancedPricingSimple 1' => [
                [
                    'customer_group_id' => \Magento\Customer\Model\Group::CUST_GROUP_ALL,
                    'value'             => '300.0000',
                    'qty'               => '10.0000',
                    'percentage_value'  => null
                ],
                [
                    'customer_group_id' => '1',
                    'value'             => '11.0000',
                    'qty'               => '11.0000',
                    'percentage_value'  => null
                ],
                [
                    'customer_group_id' => '3',
                    'value'             => '14.0000',
                    'qty'               => '14.0000',
                    'percentage_value'  => null
                ],
                [
                    'customer_group_id' => \Magento\Customer\Model\Group::CUST_GROUP_ALL,
                    'value'             => '160.5000',
                    'qty'               => '20.0000',
                    'percentage_value'  => '50.0000'
                ]
            ],
            'AdvancedPricingSimple 2' => [
                [
                    'customer_group_id' => \Magento\Customer\Model\Group::CUST_GROUP_ALL,
                    'value'             => '1000000.0000',
                    'qty'               => '100.0000',
                    'percentage_value'  => null
                ],
                [
                    'customer_group_id' => '0',
                    'value'             => '12.0000',
                    'qty'               => '12.0000',
                    'percentage_value'  => null
                ],
                [
                    'customer_group_id' => '2',
                    'value'             => '13.0000',
                    'qty'               => '13.0000',
                    'percentage_value'  => null
                ],
                [
                    'customer_group_id' => \Magento\Customer\Model\Group::CUST_GROUP_ALL,
                    'value'             => '327.0000',
                    'qty'               => '200.0000',
                    'percentage_value'  => '50.0000'
                ]
            ]
        ];
    }

    /**
     * @magentoDataFixture Magento/AdvancedPricingImportExport/_files/create_products.php
     * @magentoAppArea adminhtml
     */
    public function testImportAddUpdate()
    {
        // import data from CSV file
        $pathToFile = __DIR__ . '/_files/import_advanced_pricing.csv';
        $filesystem = $this->objectManager->create(
            \Magento\Framework\Filesystem::class
        );

        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => $pathToFile,
                'directory' => $directory
            ]
        );
        $errors = $this->model->setSource(
            $source
        )->setParameters(
            [
                'behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND,
                'entity' => 'advanced_pricing'
            ]
        )->validateData();

        $this->assertEquals(0, $errors->getErrorsCount(), 'Advanced pricing import validation error');
        $this->model->importData();

        /** @var \Magento\Catalog\Model\ResourceModel\Product $resource */
        $resource = $this->objectManager->get(\Magento\Catalog\Model\ResourceModel\Product::class);
        $productIdList = $resource->getProductsIdsBySkus(array_keys($this->expectedTierPrice));
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->objectManager->create(\Magento\Catalog\Model\Product::class);
        foreach ($productIdList as $sku => $productId) {
            $product->load($productId);
            $tierPriceCollection = $product->getTierPrices();
            $this->assertEquals(4, count($tierPriceCollection));
            $index = 0;
            /** @var \Magento\Catalog\Model\Product\TierPrice $tierPrice */
            foreach ($tierPriceCollection as $tierPrice) {
                $this->checkPercentageDiscount($tierPrice, $sku, $index);
                $this->assertEquals(0, $tierPrice->getExtensionAttributes()->getWebsiteId());
                $tierPriceData = $tierPrice->getData();
                unset($tierPriceData['extension_attributes']);
                $this->assertContains($tierPriceData, $this->expectedTierPrice[$sku]);
                $index ++;
            }
        }
    }

    /**
     * Check percentage discount type.
     *
     * @param \Magento\Catalog\Model\Product\TierPrice $tierPrice
     * @param string $sku
     * @param int $index
     * @return void
     */
    private function checkPercentageDiscount(
        \Magento\Catalog\Model\Product\TierPrice $tierPrice,
        $sku,
        $index
    ) {
            $this->assertEquals(
                $this->expectedTierPrice[$sku][$index]['percentage_value'],
                $tierPrice->getExtensionAttributes()->getPercentageValue()
            );
            $tierPrice->setData('percentage_value', $tierPrice->getExtensionAttributes()->getPercentageValue());
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testImportDelete()
    {
        $productRepository = $this->objectManager->create(
            \Magento\Catalog\Api\ProductRepositoryInterface::class
        );
        $index = 0;
        $ids = [];
        $origPricingData = [];
        while (isset($skus[$index])) {
            $ids[$index] = $productRepository->get($skus[$index])->getId();
            $origPricingData[$index] = $this->objectManager->create(\Magento\Catalog\Model\Product::class)
                ->load($ids[$index])
                ->getTierPrices();
            $index++;
        }

        $csvfile = uniqid('importexport_') . '.csv';

        /** @var \Magento\AdvancedPricingImportExport\Model\Export\AdvancedPricing $exportModel */
        $exportModel = $this->objectManager->create(
            \Magento\AdvancedPricingImportExport\Model\Export\AdvancedPricing::class
        );
        $exportModel->setWriter(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                \Magento\ImportExport\Model\Export\Adapter\Csv::class,
                ['fileSystem' => $this->fileSystem, 'destination' => $csvfile]
            )
        );
        $this->assertNotEmpty($exportModel->export());

        $directory = $this->fileSystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => $csvfile,
                'directory' => $directory
            ]
        );
        $errors = $this->model->setParameters(
            [
                'behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE,
                'entity' => 'advanced_pricing'
            ]
        )->setSource(
            $source
        )->validateData();

        $this->assertTrue(
            $errors->getErrorsCount() == 0,
            'Advanced Pricing import error, imported from file:' . $csvfile
        );
        $this->model->importData();

        while ($index > 0) {
            $index--;
            $newPricingData = $this->objectManager->create(\Magento\Catalog\Model\Product::class)
                ->load($ids[$index])
                ->getTierPrices();
            $this->assertEquals(0, count($newPricingData));
        }
    }

    /**
     * @magentoDataFixture Magento/AdvancedPricingImportExport/_files/create_products.php
     * @magentoAppArea adminhtml
     */
    public function testImportReplace()
    {
        // import data from CSV file
        $pathToFile = __DIR__ . '/_files/import_advanced_pricing.csv';
        $filesystem = $this->objectManager->create(
            \Magento\Framework\Filesystem::class
        );

        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => $pathToFile,
                'directory' => $directory
            ]
        );
        $errors = $this->model->setSource(
            $source
        )->setParameters(
            [
                'behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE,
                'entity' => 'advanced_pricing'
            ]
        )->validateData();

        $this->assertEquals(0, $errors->getErrorsCount(), 'Advanced pricing import validation error');
        $this->model->importData();

        /** @var \Magento\Catalog\Model\ResourceModel\Product $resource */
        $resource = $this->objectManager->get(\Magento\Catalog\Model\ResourceModel\Product::class);
        $productIdList = $resource->getProductsIdsBySkus(array_keys($this->expectedTierPrice));
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->objectManager->create(\Magento\Catalog\Model\Product::class);
        foreach ($productIdList as $sku => $productId) {
            $product->load($productId);
            $tierPriceCollection = $product->getTierPrices();
            $this->assertEquals(4, count($tierPriceCollection));
            $index = 0;
            /** @var \Magento\Catalog\Model\Product\TierPrice $tierPrice */
            foreach ($tierPriceCollection as $tierPrice) {
                $this->checkPercentageDiscount($tierPrice, $sku, $index);
                $this->assertEquals(0, $tierPrice->getExtensionAttributes()->getWebsiteId());
                $tierPriceData = $tierPrice->getData();
                unset($tierPriceData['extension_attributes']);
                $this->assertContains($tierPriceData, $this->expectedTierPrice[$sku]);
                $index ++;
            }
        }
    }
}
