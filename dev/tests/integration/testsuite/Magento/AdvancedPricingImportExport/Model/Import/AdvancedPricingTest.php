<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedPricingImportExport\Model\Import;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Test\Fixture\SelectAttribute;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\Import\Source\Csv;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory;
use Magento\Catalog\Api\Data\ProductTierPriceExtensionFactory;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\AppArea;

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

    protected function setUp(): void
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
                    'value'             => '300.000000',
                    'qty'               => '10.0000',
                    'percentage_value'  => null
                ],
                [
                    'customer_group_id' => '1',
                    'value'             => '11.000000',
                    'qty'               => '11.0000',
                    'percentage_value'  => null
                ],
                [
                    'customer_group_id' => '3',
                    'value'             => '14.000000',
                    'qty'               => '14.0000',
                    'percentage_value'  => null
                ],
                [
                    'customer_group_id' => \Magento\Customer\Model\Group::CUST_GROUP_ALL,
                    'value'             => 160.5,
                    'qty'               => '20.0000',
                    'percentage_value'  => '50.00'
                ]
            ],
            'AdvancedPricingSimple 2' => [
                [
                    'customer_group_id' => \Magento\Customer\Model\Group::CUST_GROUP_ALL,
                    'value'             => '1000000.000000',
                    'qty'               => '100.0000',
                    'percentage_value'  => null
                ],
                [
                    'customer_group_id' => '0',
                    'value'             => '12.000000',
                    'qty'               => '12.0000',
                    'percentage_value'  => null
                ],
                [
                    'customer_group_id' => '2',
                    'value'             => '13.000000',
                    'qty'               => '13.0000',
                    'percentage_value'  => null
                ],
                [
                    'customer_group_id' => \Magento\Customer\Model\Group::CUST_GROUP_ALL,
                    'value'             => 327.0,
                    'qty'               => '200.0000',
                    'percentage_value'  => '50.00'
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
        $errors = $this->doImport($pathToFile, DirectoryList::ROOT, Import::BEHAVIOR_APPEND, true);
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
            $this->assertCount(4, $tierPriceCollection);
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
            (int)$this->expectedTierPrice[$sku][$index]['percentage_value'],
            (int)$tierPrice->getExtensionAttributes()->getPercentageValue()
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
        $skus = ['simple'];
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
                ['fileSystem' => $this->fileSystem]
            )
        );
        $exportContent = $exportModel->export();
        $this->assertNotEmpty($exportContent);

        $directory = $this->fileSystem->getDirectoryWrite(DirectoryList::VAR_IMPORT_EXPORT);
        $directory->getDriver()->filePutContents($directory->getAbsolutePath($csvfile), $exportContent);

        $errors = $this->doImport($csvfile, DirectoryList::VAR_IMPORT_EXPORT, Import::BEHAVIOR_DELETE, true);

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
            $this->assertCount(0, $newPricingData);
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
        $errors = $this->doImport($pathToFile, DirectoryList::ROOT, Import::BEHAVIOR_REPLACE, true);

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
            $this->assertCount(4, $tierPriceCollection);
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
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store catalog/price/scope 1
     * @magentoDataFixture Magento/AdvancedPricingImportExport/_files/create_products.php
     * @param array $dbData
     * @param array $importData
     * @param string $importBehavior
     * @param array $invalidRows
     * @dataProvider importValidationDuplicateWithSameBaseCurrencyDataProvider
     */
    public function testImportValidationDuplicateWithSameBaseCurrency(
        array $dbData,
        array $importData,
        string $importBehavior,
        array $invalidRows
    ) {
        $this->createTierPrices($dbData);
        $pathToFile = $this->generateImportFile($importData);
        $errors = $this->doImport($pathToFile, DirectoryList::VAR_DIR, $importBehavior);
        $rows = $errors->getRowsGroupedByErrorCode(['duplicateTierPrice'], [], false);
        $this->assertEquals($invalidRows, $rows['duplicateTierPrice'] ?? []);
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store catalog/price/scope 1
     * @magentoConfigFixture base_website catalog/price/scope 1
     * @magentoConfigFixture base_website currency/options/base EUR
     * @magentoDataFixture Magento/AdvancedPricingImportExport/_files/create_products.php
     * @param array $dbData
     * @param array $importData
     * @param string $importBehavior
     * @param array $invalidRows
     * @dataProvider importValidationDuplicateWithDifferentBaseCurrencyDataProvider
     */
    public function testImportValidationDuplicateWithDifferentBaseCurrency(
        array $dbData,
        array $importData,
        string $importBehavior,
        array $invalidRows
    ) {
        $this->createTierPrices($dbData);
        $pathToFile = $this->generateImportFile($importData);
        $errors = $this->doImport($pathToFile, DirectoryList::VAR_DIR, $importBehavior);
        $rows = $errors->getRowsGroupedByErrorCode(['duplicateTierPrice'], [], false);
        $this->assertEquals($invalidRows, $rows['duplicateTierPrice'] ?? []);
    }

    /**
     * @return array[]
     */
    public function importValidationDuplicateWithSameBaseCurrencyDataProvider(): array
    {
        return require __DIR__ . '/_files/import_validation_duplicate_same_currency_data_provider.php';
    }

    /**
     * @return array[]
     */
    public function importValidationDuplicateWithDifferentBaseCurrencyDataProvider(): array
    {
        return require __DIR__ . '/_files/import_validation_duplicate_diff_currency_data_provider.php';
    }

    /**
     * @param string $directoryCode
     * @param string $file
     * @param string $behavior
     * @param bool $validateOnly
     * @return ProcessingErrorAggregatorInterface
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function doImport(
        string $file,
        string $directoryCode = DirectoryList::ROOT,
        string $behavior = Import::BEHAVIOR_APPEND,
        bool $validateOnly = false,
        string $entity = 'advanced_pricing'
    ): ProcessingErrorAggregatorInterface {
        /** @var Filesystem $filesystem */
        $filesystem = $this->objectManager->create(Filesystem::class);
        $directory = $filesystem->getDirectoryWrite($directoryCode);
        $source = $this->objectManager->create(
            Csv::class,
            [
                'file' => $file,
                'directory' => $directory
            ]
        );
        $errors = $this->model->setSource($source)
            ->setParameters(
                [
                    'behavior' => $behavior,
                    'entity' => $entity
                ]
            )
            ->validateData();
        if (!$validateOnly && !$errors->getAllErrors()) {
            $this->model->importData();
        }

        return $errors;
    }

    /**
     * @param array $tierPrices
     */
    private function createTierPrices(array $tierPrices)
    {
        $productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $tierPriceFactory = $this->objectManager->get(ProductTierPriceInterfaceFactory::class);
        $tpExtensionAttributesFactory = $this->objectManager->get(ProductTierPriceExtensionFactory::class);
        $storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $productTierPrices = [];
        foreach ($tierPrices as $price) {
            $sku = $price['sku'];
            $websiteId = 0;
            $websiteCode = $price['website_id'];
            $percentageValue = $price['percentage_value'] ?? null;
            unset($price['sku'], $price['website_id'], $price['percentage_value']);
            if ($websiteCode !== 0) {
                $websiteId = $storeManager->getWebsite($websiteCode)->getId();
            }
            $tierPriceExtensionAttributes = $tpExtensionAttributesFactory->create();
            $tierPriceExtensionAttributes->setWebsiteId($websiteId);
            $tierPriceExtensionAttributes->setPercentageValue($percentageValue);
            $productTierPrices[$sku][] = $tierPriceFactory->create(['data' => $price])
                ->setExtensionAttributes($tierPriceExtensionAttributes);
        }

        foreach ($productTierPrices as $sku => $prices) {
            $product = $productRepository->get($sku, true, null, true);
            $product->setTierPrices($prices);
            $productRepository->save($product);
        }
    }

    /**
     * @param array $data
     * @return string
     */
    private function generateImportFile(array $data): string
    {
        $fields = [
            'sku',
            'tier_price_website',
            'tier_price_customer_group',
            'tier_price_qty',
            'tier_price',
            'tier_price_value_type',
        ];
        $objectManager = Bootstrap::getObjectManager();
        /** @var Filesystem $filesystem */
        $filesystem = $objectManager->get(Filesystem::class);
        $varDir = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $tmpFilename = uniqid('test_import_advanced_pricing_') . '.csv';
        $stream = $varDir->openFile($tmpFilename, 'w+');
        $stream->lock();
        $stream->writeCsv($fields);
        $emptyRow = array_fill_keys($fields, '');
        foreach ($data as $row) {
            $row = array_replace($emptyRow, $row);
            $stream->writeCsv($row);
        }
        $stream->unlock();
        $stream->close();
        return $varDir->getAbsolutePath($tmpFilename);
    }

    /**
     * For checking if correct add and update count are being displayed after importing file having 100+ records
     */
    #[
        DataFixture(
            SelectAttribute::class,
            [
                'attribute_code' => 'size',
                'default_frontend_label' => 'Size',
                'scope' => 'global',
                'options' => [28,29,30,31,32,33,34,36,38]
            ],
            'attr1'
        ),
        DataFixture(
            SelectAttribute::class,
            [
                'attribute_code' => 'colors',
                'default_frontend_label' => 'Colors',
                'scope' => 'global',
                'options' => ["Red","Green","Yellow","Blue","Orange"]
            ]
        ),
        AppArea('adminhtml')
    ]
    public function testImportAddUpdateCounts()
    {
        $this->model = $this->objectManager->create(\Magento\CatalogImportExport\Model\Import\Product::class);

        // Import product data from CSV file
        $productFilePath = __DIR__ . '/_files/import_catalog_product.csv';
        $errors = $this->doImport(
            $productFilePath,
            DirectoryList::ROOT,
            Import::BEHAVIOR_ADD_UPDATE,
            true,
            'catalog_product'
        );
        print_r($errors->getAllErrors());
        $this->assertEquals(0, $errors->getErrorsCount(), 'Product import validation error');
        $this->model->importData();

        $this->assertEquals(64, $this->model->getCreatedItemsCount(), 'Products create item count');
        $this->assertEquals(0, $this->model->getUpdatedItemsCount(), 'Products update item count');

        // Import advance pricing data from CSV file
        $this->model = $this->objectManager->create(
            \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::class
        );
        $pathToFile = __DIR__ . '/_files/import_advanced_pricing_for_additional_attributes_products.csv';
        $errors = $this->doImport($pathToFile, DirectoryList::ROOT, Import::BEHAVIOR_APPEND, true);
        print_r($errors->getAllErrors());
        $this->assertEquals(0, $errors->getErrorsCount(), 'Advanced pricing import validation error');
        $this->model->importData();

        $this->assertEquals(127, $this->model->getCreatedItemsCount(), 'Advance pricing create count1');
        $this->assertEquals(0, $this->model->getUpdatedItemsCount(), 'Advance pricing update count1');

        // Initializing again, since old model object holds old count
        // Import advance pricing data from CSV file
        $this->model = $this->objectManager->create(
            \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::class
        );
        $this->doImport($pathToFile, DirectoryList::ROOT, Import::BEHAVIOR_APPEND);
        $this->assertEquals(0, $this->model->getCreatedItemsCount(), 'Advance pricing create count2');
        $this->assertEquals(127, $this->model->getUpdatedItemsCount(), 'Advance pricing update count2');
    }
}
