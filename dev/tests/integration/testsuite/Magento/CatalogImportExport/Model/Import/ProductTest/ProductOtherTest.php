<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Model\Import\ProductTest;

use Magento\Catalog\Helper\Data as CatalogConfig;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Test\Fixture\Attribute as AttributeFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\CatalogImportExport\Model\Import\ProductTestBase;
use Magento\CatalogInventory\Model\StockRegistry;
use Magento\Directory\Helper\Data as DirectoryData;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\ImportExport\Helper\Data;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\Import\Source\Csv;
use Magento\ImportExport\Test\Fixture\CsvFile as CsvFileFixture;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\Translation\Test\Fixture\Translation;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollection;

/**
 * Integration test for \Magento\CatalogImportExport\Model\Import\Product class.
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 * @magentoAppArea adminhtml
 * @magentoDataFixtureBeforeTransaction Magento/Catalog/_files/enable_reindex_schedule.php
 * @magentoDataFixtureBeforeTransaction Magento/Catalog/_files/enable_catalog_product_reindex_schedule.php
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductOtherTest extends ProductTestBase
{
    /**
     * Test if visibility properly saved after import
     *
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     * @magentoAppIsolation enabled
     */
    public function testSaveProductsVisibility()
    {
        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Api\ProductRepositoryInterface::class
        );
        $id1 = $productRepository->get('simple1')->getId();
        $id2 = $productRepository->get('simple2')->getId();
        $id3 = $productRepository->get('simple3')->getId();
        $existingProductIds = [$id1, $id2, $id3];
        $productsBeforeImport = [];
        foreach ($existingProductIds as $productId) {
            $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                \Magento\Catalog\Model\Product::class
            );
            $product->load($productId);
            $productsBeforeImport[] = $product;
        }
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Framework\Filesystem::class);
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => __DIR__ . '/../_files/products_to_import.csv',
                'directory' => $directory
            ]
        );
        $this->_model->setParameters(
            ['behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND, 'entity' => 'catalog_product']
        );
        $this->_model->setSource($source);
        $errors = $this->_model->validateData();
        $this->assertTrue($errors->getErrorsCount() == 0);
        $this->_model->importData();
        /** @var $productBeforeImport \Magento\Catalog\Model\Product */
        foreach ($productsBeforeImport as $productBeforeImport) {
            /** @var $productAfterImport \Magento\Catalog\Model\Product */
            $productAfterImport = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                \Magento\Catalog\Model\Product::class
            );
            $productAfterImport->load($productBeforeImport->getId());
            $this->assertEquals($productBeforeImport->getVisibility(), $productAfterImport->getVisibility());
        }
    }

    /**
     * Test if datetime properly saved after import
     *
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     * @magentoAppIsolation enabled
     */
    public function testSaveDatetimeAttribute()
    {
        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Api\ProductRepositoryInterface::class
        );
        $id1 = $productRepository->get('simple1')->getId();
        $id2 = $productRepository->get('simple2')->getId();
        $id3 = $productRepository->get('simple3')->getId();
        $existingProductIds = [$id1, $id2, $id3];
        $productsBeforeImport = [];
        foreach ($existingProductIds as $productId) {
            $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                \Magento\Catalog\Model\Product::class
            );
            $product->load($productId);
            $productsBeforeImport[$product->getSku()] = $product;
        }
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Framework\Filesystem::class);
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => __DIR__ . '/../_files/products_to_import_with_datetime.csv',
                'directory' => $directory
            ]
        );
        $this->_model->setParameters(
            ['behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND, 'entity' => 'catalog_product']
        );
        $this->_model->setSource($source);
        $errors = $this->_model->validateData();
        $this->assertTrue($errors->getErrorsCount() == 0);
        $this->_model->importData();
        $source->rewind();
        foreach ($source as $row) {
            /** @var $productAfterImport \Magento\Catalog\Model\Product */
            $productBeforeImport = $productsBeforeImport[$row['sku']];

            /** @var $productAfterImport \Magento\Catalog\Model\Product */
            $productAfterImport = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                \Magento\Catalog\Model\Product::class
            );
            $productAfterImport->load($productBeforeImport->getId());
            $this->assertEquals(
                strtotime(date('m/d/Y', strtotime($row['news_from_date']))),
                strtotime($productAfterImport->getNewsFromDate())
            );
            $this->assertEquals(
                strtotime($row['news_to_date']),
                strtotime($productAfterImport->getNewsToDate())
            );
            unset($productAfterImport);
        }
        unset($productsBeforeImport, $product);
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testProductWithLinks()
    {
        $linksData = [
            'upsell' => [
                'simple1' => '3',
                'simple3' => '1'
            ],
            'crosssell' => [
                'simple2' => '1',
                'simple3' => '2'
            ],
            'related' => [
                'simple1' => '2',
                'simple2' => '1'
            ]
        ];
        // import data from CSV file
        $pathToFile = __DIR__ . '/../_files/products_to_import_with_product_links.csv';
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
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
        $this->_model->setSource($source);
        $this->_model->setParameters([
            'behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND,
            'entity' => 'catalog_product'
        ]);
        $errors = $this->_model->validateData();
        $this->assertTrue($errors->getErrorsCount() == 0);
        $this->_model->importData();
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $resource = $objectManager->get(\Magento\Catalog\Model\ResourceModel\Product::class);
        $productId = $resource->getIdBySku('simple4');
        /** @var \Magento\Catalog\Model\Product $product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product::class
        );
        $product->load($productId);
        $productLinks = [
            'upsell' => $product->getUpSellProducts(),
            'crosssell' => $product->getCrossSellProducts(),
            'related' => $product->getRelatedProducts()
        ];
        $importedProductLinks = [];
        foreach ($productLinks as $linkType => $linkedProducts) {
            foreach ($linkedProducts as $linkedProductData) {
                $importedProductLinks[$linkType][$linkedProductData->getSku()] = $linkedProductData->getPosition();
            }
        }
        $this->assertEquals($linksData, $importedProductLinks);
    }

    /**
     * @magentoConfigFixture default/catalog/seo/generate_category_product_rewrites 1
     * @magentoDataFixture Magento/Catalog/_files/product_without_options.php
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testUpdateUrlRewritesOnImport()
    {
        $filesystem = $this->objectManager->create(\Magento\Framework\Filesystem::class);
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => __DIR__ . '/../_files/products_to_import_with_category.csv',
                'directory' => $directory
            ]
        );
        $this->_model->setParameters([
            'behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND,
            'entity' => \Magento\Catalog\Model\Product::ENTITY
        ]);
        $this->_model->setSource($source);
        $errors = $this->_model->validateData();
        $this->assertTrue($errors->getErrorsCount() == 0);
        $this->_model->importData();
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->objectManager->create(\Magento\Catalog\Model\ProductRepository::class)->get('simple');
        $listOfProductUrlKeys = [
            sprintf('%s.html', $product->getUrlKey()),
            sprintf('men/tops/%s.html', $product->getUrlKey()),
            sprintf('men/%s.html', $product->getUrlKey())
        ];
        $repUrlRewriteCol = $this->objectManager->create(
            UrlRewriteCollection::class
        );
        /** @var UrlRewriteCollection $collUrlRewrite */
        $collUrlRewrite = $repUrlRewriteCol->addFieldToSelect(['request_path'])
            ->addFieldToFilter('entity_id', ['eq'=> $product->getEntityId()])
            ->addFieldToFilter('entity_type', ['eq'=> 'product'])
            ->load();
        $listOfUrlRewriteIds = $collUrlRewrite->getAllIds();
        $this->assertCount(3, $collUrlRewrite);
        foreach ($listOfUrlRewriteIds as $key => $id) {
            $this->assertEquals(
                $listOfProductUrlKeys[$key],
                $collUrlRewrite->getItemById($id)->getRequestPath()
            );
        }
    }

    /**
     * @magentoConfigFixture default/catalog/seo/generate_category_product_rewrites 0
     * @magentoDataFixture Magento/Catalog/_files/product_without_options.php
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testUpdateUrlRewritesOnImportWithoutGenerateCategoryProductRewrites()
    {
        $filesystem = $this->objectManager->create(\Magento\Framework\Filesystem::class);
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => __DIR__ . '/../_files/products_to_import_with_category.csv',
                'directory' => $directory
            ]
        );
        $this->_model->setParameters([
            'behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND,
            'entity' => \Magento\Catalog\Model\Product::ENTITY
        ]);
        $this->_model->setSource($source);
        $errors = $this->_model->validateData();
        $this->assertTrue($errors->getErrorsCount() == 0);
        $this->_model->importData();
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->objectManager->create(\Magento\Catalog\Model\ProductRepository::class)->get('simple');
        $listOfProductUrlKeys = [
            sprintf('%s.html', $product->getUrlKey()),
            sprintf('men/tops/%s.html', $product->getUrlKey()),
            sprintf('men/%s.html', $product->getUrlKey())
        ];
        $repUrlRewriteCol = $this->objectManager->create(
            UrlRewriteCollection::class
        );
        /** @var UrlRewriteCollection $collUrlRewrite */
        $collUrlRewrite = $repUrlRewriteCol->addFieldToSelect(['request_path'])
            ->addFieldToFilter('entity_id', ['eq'=> $product->getEntityId()])
            ->addFieldToFilter('entity_type', ['eq'=> 'product'])
            ->load();
        $listOfUrlRewriteIds = $collUrlRewrite->getAllIds();
        $this->assertCount(1, $collUrlRewrite);
        foreach ($listOfUrlRewriteIds as $key => $id) {
            $this->assertEquals(
                $listOfProductUrlKeys[$key],
                $collUrlRewrite->getItemById($id)->getRequestPath()
            );
        }
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testProductWithUseConfigSettings()
    {
        $products = [
            'simple1' => true,
            'simple2' => true,
            'simple3' => false
        ];
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Framework\Filesystem::class);
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => __DIR__ . '/../_files/products_to_import_with_use_config_settings.csv',
                'directory' => $directory
            ]
        );

        $errors = $this->_model->setParameters(
            ['behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND, 'entity' => 'catalog_product']
        )->setSource(
            $source
        )->validateData();

        $this->assertTrue($errors->getErrorsCount() == 0);
        $this->_model->importData();

        foreach ($products as $sku => $manageStockUseConfig) {
            /** @var StockRegistry $stockRegistry */
            $stockRegistry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                StockRegistry::class
            );
            $stockItem = $stockRegistry->getStockItemBySku($sku);
            $this->assertEquals($manageStockUseConfig, $stockItem->getUseConfigManageStock());
        }
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/multiselect_attribute_with_incorrect_values.php
     * @magentoDataFixture Magento/Catalog/_files/product_text_attribute.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testProductWithWrappedAdditionalAttributes()
    {
        $filesystem = $this->objectManager->create(\Magento\Framework\Filesystem::class);
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => __DIR__ . '/../_files/products_to_import_with_additional_attributes.csv',
                'directory' => $directory
            ]
        );
        $errors = $this->_model->setParameters(
            [
                'behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND,
                'entity' => 'catalog_product',
                \Magento\ImportExport\Model\Import::FIELDS_ENCLOSURE => 1
            ]
        )->setSource(
            $source
        )->validateData();

        $this->assertTrue($errors->getErrorsCount() == 0);

        $this->_model->importData();

        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Api\ProductRepositoryInterface::class
        );

        /** @var \Magento\Eav\Api\AttributeOptionManagementInterface $multiselectOptions */
        $multiselectOptions = $this->objectManager->get(\Magento\Eav\Api\AttributeOptionManagementInterface::class)
            ->getItems(\Magento\Catalog\Model\Product::ENTITY, 'multiselect_attribute');

        $product1 = $productRepository->get('simple1');
        $this->assertEquals('\'", =|', $product1->getData('text_attribute'));
        $this->assertEquals(
            implode(',', [$multiselectOptions[3]->getValue(), $multiselectOptions[2]->getValue()]),
            $product1->getData('multiselect_attribute')
        );

        $product2 = $productRepository->get('simple2');
        $this->assertEquals('', $product2->getData('text_attribute'));
        $this->assertEquals(
            implode(',', [$multiselectOptions[1]->getValue(), $multiselectOptions[2]->getValue()]),
            $product2->getData('multiselect_attribute')
        );
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_text_attribute.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @dataProvider importWithJsonAndMarkupTextAttributeDataProvider
     * @param string $productSku
     * @param string $expectedResult
     * @return void
     */
    public function testImportWithJsonAndMarkupTextAttribute(string $productSku, string $expectedResult): void
    {
        // added by _files/product_import_with_json_and_markup_attributes.csv
        $this->importedProducts = [
            'SkuProductWithJson',
            'SkuProductWithMarkup',
        ];

        $importParameters =[
            'behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND,
            'entity' => 'catalog_product',
            \Magento\ImportExport\Model\Import::FIELDS_ENCLOSURE => 0
        ];
        $filesystem = $this->objectManager->create(\Magento\Framework\Filesystem::class);
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => __DIR__ . '/../_files/products_to_import_with_json_and_markup_attributes.csv',
                'directory' => $directory
            ]
        );
        $this->_model->setParameters($importParameters);
        $this->_model->setSource($source);
        $errors = $this->_model->validateData();
        $this->assertTrue($errors->getErrorsCount() == 0);
        $this->_model->importData();
        $productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Api\ProductRepositoryInterface::class
        );
        $product = $productRepository->get($productSku);
        $this->assertEquals($expectedResult, $product->getData('text_attribute'));
    }

    /**
     * @return array
     */
    public static function importWithJsonAndMarkupTextAttributeDataProvider(): array
    {
        return [
            'import of attribute with json' => [
                'SkuProductWithJson',
                '{"type": "basic", "unit": "inch", "sign": "(\")", "size": "1.5\""}'
            ],
            'import of attribute with markup' => [
                'SkuProductWithMarkup',
                '<div data-content>Element type is basic, measured in inches ' .
                '(marked with sign (\")) with size 1.5\", mid-price range</div>'
            ],
        ];
    }

    /**
     * Test if we can change attribute set for product via import.
     *
     * @magentoDataFixture Magento/Catalog/_files/attribute_set_with_renamed_group.php
     * @magentoDataFixture Magento/Catalog/_files/product_without_options.php
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     * @magentoDbIsolation enabled
     */
    public function testImportDataChangeAttributeSet()
    {
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Framework\Filesystem::class);

        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);

        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => __DIR__ . '/../_files/products_to_import_with_new_attribute_set.csv',
                'directory' => $directory
            ]
        );
        $errors = $this->_model->setParameters(
            [
                'behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND,
                'entity' => \Magento\Catalog\Model\Product::ENTITY
            ]
        )->setSource(
            $source
        )->validateData();

        $this->assertTrue($errors->getErrorsCount() == 0);

        $this->_model->importData();

        /** @var \Magento\Catalog\Model\Product[] $products */
        $products[] = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Catalog\Model\ProductRepository::class)->get('simple');
        $products[] = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Catalog\Model\ProductRepository::class)->get('simple2');

        /** @var \Magento\Catalog\Model\Config $catalogConfig */
        $catalogConfig = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Catalog\Model\Config::class);

        /** @var \Magento\Eav\Model\Config $eavConfig */
        $eavConfig = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Eav\Model\Config::class);

        $entityTypeId = (int)$eavConfig->getEntityType(\Magento\Catalog\Model\Product::ENTITY)
            ->getId();

        foreach ($products as $product) {
            $attributeSetName = $catalogConfig->getAttributeSetName($entityTypeId, $product->getAttributeSetId());
            $this->assertEquals('attribute_set_test', $attributeSetName);
        }
    }

    /**
     * Test importing products with changed SKU letter case.
     */
    public function testImportWithDifferentSkuCase()
    {
        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Api\ProductRepositoryInterface::class
        );
        /** @var \Magento\Framework\Api\SearchCriteria $searchCriteria */
        $searchCriteria = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Framework\Api\SearchCriteria::class);

        $importedPrices = [
            'simple1' => 25,
            'simple2' => 34,
            'simple3' => 58,
        ];
        $updatedPrices = [
            'simple1' => 111,
            'simple2' => 222,
            'simple3' => 333,
        ];

        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Framework\Filesystem::class);

        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);

        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => __DIR__ . '/../_files/products_to_import.csv',
                'directory' => $directory
            ]
        );
        $errors = $this->_model->setParameters(
            [
                'behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE,
                'entity' => \Magento\Catalog\Model\Product::ENTITY
            ]
        )->setSource($source)->validateData();

        $this->assertTrue($errors->getErrorsCount() == 0);

        $this->_model->importData();
        $this->createNewModel();
        $this->assertCount(
            3,
            $productRepository->getList($searchCriteria)->getItems()
        );
        foreach ($importedPrices as $sku => $expectedPrice) {
            $this->assertEquals($expectedPrice, $productRepository->get($sku)->getPrice());
        }

        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => __DIR__ . '/../_files/products_to_import_with_changed_sku_case.csv',
                'directory' => $directory
            ]
        );
        $errors = $this->_model->setParameters(
            [
                'behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE,
                'entity' => \Magento\Catalog\Model\Product::ENTITY
            ]
        )->setSource($source)->validateData();

        $this->assertTrue($errors->getErrorsCount() == 0);

        $this->_model->importData();

        $this->assertCount(
            3,
            $productRepository->getList($searchCriteria)->getItems(),
            'Ensures that new products were not created'
        );
        foreach ($updatedPrices as $sku => $expectedPrice) {
            $this->assertEquals(
                $expectedPrice,
                $productRepository->get($sku, false, null, true)->getPrice(),
                'Check that all products were updated'
            );
        }
    }

    /**
     * Checks possibility to double importing products using the same import file.
     *
     * Bunch size is using to test importing the same product that will be chunk to different bunches.
     * Example:
     * - first bunch
     * product-sku,default-store
     * product-sku,second-store
     * - second bunch
     * product-sku,third-store
     *
     * @magentoDbIsolation disabled
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoDataFixture Magento/Store/_files/second_store.php
     */
    public function testCheckDoubleImportOfProducts()
    {
        $this->importedProducts = [
            'simple1',
            'simple2',
            'simple3',
        ];
        /** @var SearchCriteria $searchCriteria */
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $this->assertTrue($this->importFile('products_with_two_store_views.csv', 2));
        $productsAfterFirstImport = $this->productRepository->getList($searchCriteria)->getItems();
        $this->assertCount(3, $productsAfterFirstImport);
        $this->assertTrue($this->importFile('products_with_two_store_views.csv', 2));
        $productsAfterSecondImport = $this->productRepository->getList($searchCriteria)->getItems();
        $this->assertCount(3, $productsAfterSecondImport);
    }

    /**
     * Checks that product related links added for all bunches properly after products import
     */
    public function testImportProductsWithLinksInDifferentBunches()
    {
        $this->importedProducts = [
            'simple1',
            'simple2',
            'simple3',
            'simple4',
            'simple5',
            'simple6',
        ];
        $importExportData = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $importExportData->expects($this->atLeastOnce())
            ->method('getBunchSize')
            ->willReturn(5);
        $this->_model = $this->objectManager->create(
            \Magento\CatalogImportExport\Model\Import\Product::class,
            ['importExportData' => $importExportData]
        );
        $linksData = [
            'related' => [
                'simple1' => '2',
                'simple2' => '1'
            ]
        ];
        $pathToFile = __DIR__ . '/../_files/products_to_import_with_related.csv';
        $filesystem = $this->objectManager->create(Filesystem::class);
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            Csv::class,
            [
                'file' => $pathToFile,
                'directory' => $directory
            ]
        );
        $this->_model->setSource($source);
        $this->_model->setParameters([
            'behavior' => Import::BEHAVIOR_APPEND,
            'entity' => 'catalog_product'
        ]);
        $errors = $this->_model->validateData();
        $this->assertTrue($errors->getErrorsCount() == 0);
        $this->_model->importData();
        $resource = $this->objectManager->get(ProductResource::class);
        $productId = $resource->getIdBySku('simple6');
        /** @var Product $product */
        $product = $this->objectManager->create(Product::class);
        $product->load($productId);
        $productLinks = [
            'related' => $product->getRelatedProducts()
        ];
        $importedProductLinks = [];
        foreach ($productLinks as $linkType => $linkedProducts) {
            foreach ($linkedProducts as $linkedProductData) {
                $importedProductLinks[$linkType][$linkedProductData->getSku()] = $linkedProductData->getPosition();
            }
        }
        $this->assertEquals($linksData, $importedProductLinks);
    }

    /**
     * Test that product tax classes "none", "0" are imported correctly
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     * @magentoAppIsolation enabled
     */
    public function testImportProductWithTaxClassNone(): void
    {
        $pathToFile = __DIR__ . '/../_files/product_tax_class_none_import.csv';
        $importModel = $this->createImportModel($pathToFile);
        $this->assertErrorsCount(0, $importModel->validateData());
        $importModel->importData();
        $simpleProduct = $this->getProductBySku('simple');
        $this->assertSame('0', (string) $simpleProduct->getTaxClassId());
        $simpleProduct = $this->getProductBySku('simple2');
        $this->assertSame('0', (string) $simpleProduct->getTaxClassId());
    }

    #[
        Config(CatalogConfig::XML_PATH_PRICE_SCOPE, CatalogConfig::PRICE_SCOPE_WEBSITE, ScopeInterface::SCOPE_STORE),
        DataFixture(ProductFixture::class, ['price' => 10], 'product'),
        DataFixture(
            CsvFileFixture::class,
            [
                'rows' => [
                    ['sku', 'store_view_code', 'price'],
                    ['$product.sku$', 'default', '9'],
                    ['$product.sku$', 'default', '8'],
                ]
            ],
            'file'
        ),
    ]
    public function testImportPriceInStoreViewShouldNotOverrideDefaultScopePrice(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $sku = $fixtures->get('product')->getSku();
        $pathToFile = $fixtures->get('file')->getAbsolutePath();
        $importModel = $this->createImportModel($pathToFile);
        $this->assertErrorsCount(0, $importModel->validateData());
        $importModel->importData();
        $product = $this->productRepository->get($sku, storeId: Store::DEFAULT_STORE_ID, forceReload: true);
        $this->assertEquals(10, $product->getPrice());
        $product = $this->productRepository->get($sku, storeId: Store::DISTRO_STORE_ID, forceReload: true);
        $this->assertEquals(9, $product->getPrice());
    }

    #[
        DataFixture(
            Translation::class,
            [
                'string' => 'Not Visible Individually',
                'translate' => 'Nicht individuell sichtbar',
                'locale' => 'de_DE',
            ]
        ),
        DataFixture(ProductFixture::class, as: 'p1'),
        DataFixture(
            CsvFileFixture::class,
            [
                'rows' => [
                    ['sku', 'visibility'],
                    ['$p1.sku$', 'Nicht individuell sichtbar'],
                ]
            ],
            'file'
        )
    ]
    public function testImportWithSpecificLocale(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $p1 = $fixtures->get('p1');
        $pathToFile = $fixtures->get('file')->getAbsolutePath();
        $importModel = $this->createImport($pathToFile, ['locale' => 'de_DE']);
        $this->assertErrorsCount(0, $importModel->getErrorAggregator());
        $importModel->importSource();
        $simpleProduct = $this->getProductBySku($p1->getSku());
        $this->assertEquals(Product\Visibility::VISIBILITY_NOT_VISIBLE, (int) $simpleProduct->getVisibility());
    }

    #[
        Config(DirectoryData::XML_PATH_DEFAULT_TIMEZONE, 'America/Chicago', ScopeConfigInterface::SCOPE_TYPE_DEFAULT),
        DataFixture(
            AttributeFixture::class,
            ['frontend_input' => 'date', 'backend_type' => 'datetime', 'attribute_code' => 'date_attr'],
            'date_attr'
        ),
        DataFixture(
            AttributeFixture::class,
            ['frontend_input' => 'datetime', 'backend_type' => 'datetime', 'attribute_code' => 'datetime_attr'],
            'datetime_attr'
        ),
        DataFixture(
            ProductFixture::class,
            ['datetime_attr' => '2015-07-19 08:30:00', 'date_attr' => '2017-02-07'],
            'product'
        ),
        DataFixture(
            CsvFileFixture::class,
            [
                'rows' => [
                    ['sku', 'store_view_code', 'product_type', 'additional_attributes'],
                    ['$product.sku$', 'default', 'simple', 'datetime_attr=10/27/23, 1:15 PM,date_attr=12/16/23'],
                ]
            ],
            'file'
        ),
    ]
    public function testImportProductWithDateAndDatetimeAttributes(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $sku = $fixtures->get('product')->getSku();
        $pathToFile = $fixtures->get('file')->getAbsolutePath();
        $product = $this->productRepository->get($sku, storeId: Store::DEFAULT_STORE_ID, forceReload: true);
        $this->assertEquals('2015-07-19 08:30:00', $product->getDatetimeAttr());
        $this->assertEquals('2017-02-07 00:00:00', $product->getDateAttr());
        $importModel = $this->createImport($pathToFile, ['locale' => 'en_US']);
        $this->assertErrorsCount(0, $importModel->getErrorAggregator());
        $importModel->importSource();
        $product = $this->productRepository->get($sku, storeId: Store::DEFAULT_STORE_ID, forceReload: true);
        $this->assertEquals('2023-10-27 18:15:00', $product->getDatetimeAttr());
        $this->assertEquals('2023-12-16 00:00:00', $product->getDateAttr());
    }

    #[
        Config(DirectoryData::XML_PATH_DEFAULT_TIMEZONE, 'America/Chicago', ScopeConfigInterface::SCOPE_TYPE_DEFAULT),
        DataFixture(
            AttributeFixture::class,
            ['frontend_input' => 'date', 'backend_type' => 'datetime', 'attribute_code' => 'date_attr'],
            'date_attr'
        ),
        DataFixture(
            AttributeFixture::class,
            ['frontend_input' => 'datetime', 'backend_type' => 'datetime', 'attribute_code' => 'datetime_attr'],
            'datetime_attr'
        ),
        DataFixture(
            ProductFixture::class,
            ['datetime_attr' => '2015-07-19 08:30:00', 'date_attr' => '2017-02-07'],
            'product'
        ),
        DataFixture(
            CsvFileFixture::class,
            [
                'rows' => [
                    ['sku', 'store_view_code', 'product_type', 'additional_attributes'],
                    ['$product.sku$', 'default', 'simple', 'datetime_attr=27.10.23, 13:15,date_attr=16.12.23'],
                ]
            ],
            'file'
        ),
    ]
    public function testImportProductWithDateAndDatetimeAttributesInLocaleFormat(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $sku = $fixtures->get('product')->getSku();
        $pathToFile = $fixtures->get('file')->getAbsolutePath();
        $product = $this->productRepository->get($sku, storeId: Store::DEFAULT_STORE_ID, forceReload: true);
        $this->assertEquals('2015-07-19 08:30:00', $product->getDatetimeAttr());
        $this->assertEquals('2017-02-07 00:00:00', $product->getDateAttr());
        $importModel = $this->createImport($pathToFile, ['locale' => 'de_DE']);
        $this->assertErrorsCount(0, $importModel->getErrorAggregator());
        $importModel->importSource();
        $product = $this->productRepository->get($sku, storeId: Store::DEFAULT_STORE_ID, forceReload: true);
        $this->assertEquals('2023-10-27 18:15:00', $product->getDatetimeAttr());
        $this->assertEquals('2023-12-16 00:00:00', $product->getDateAttr());
    }

    #[
        Config(DirectoryData::XML_PATH_DEFAULT_TIMEZONE, 'America/Chicago', ScopeConfigInterface::SCOPE_TYPE_DEFAULT),
        DataFixture(
            AttributeFixture::class,
            ['frontend_input' => 'date', 'backend_type' => 'datetime', 'attribute_code' => 'date_attr'],
            'date_attr'
        ),
        DataFixture(
            AttributeFixture::class,
            ['frontend_input' => 'datetime', 'backend_type' => 'datetime', 'attribute_code' => 'datetime_attr'],
            'datetime_attr'
        ),
        DataFixture(
            ProductFixture::class,
            ['datetime_attr' => '2015-07-19 08:30:00', 'date_attr' => '2017-02-07'],
            'product'
        ),
        DataFixture(
            CsvFileFixture::class,
            [
                'rows' => [
                    ['sku', 'store_view_code', 'product_type', 'additional_attributes'],
                    ['$product.sku$', 'default', 'simple', 'datetime_attr=2023-10-27 13:15:00,date_attr=2023-12-16'],
                ]
            ],
            'file'
        ),
    ]
    public function testImportProductWithDateAndDatetimeAttributesInInternalFormat(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $sku = $fixtures->get('product')->getSku();
        $pathToFile = $fixtures->get('file')->getAbsolutePath();
        $product = $this->productRepository->get($sku, storeId: Store::DEFAULT_STORE_ID, forceReload: true);
        $this->assertEquals('2015-07-19 08:30:00', $product->getDatetimeAttr());
        $this->assertEquals('2017-02-07 00:00:00', $product->getDateAttr());
        $importModel = $this->createImport($pathToFile, ['locale' => 'de_DE']);
        $this->assertErrorsCount(0, $importModel->getErrorAggregator());
        $importModel->importSource();
        $product = $this->productRepository->get($sku, storeId: Store::DEFAULT_STORE_ID, forceReload: true);
        $this->assertEquals('2023-10-27 18:15:00', $product->getDatetimeAttr());
        $this->assertEquals('2023-12-16 00:00:00', $product->getDateAttr());
    }

    /**
     * @param string $pathToFile
     * @param array $parameters
     * @return Import
     */
    private function createImport(string $pathToFile, array $parameters = []): Import
    {
        $filesystem = $this->objectManager->create(\Magento\Framework\Filesystem::class);
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => $pathToFile,
                'directory' => $directory
            ]
        );

        $importModel = $this->objectManager->create(
            \Magento\ImportExport\Model\Import::class
        );
        $importModel->setData(
            $parameters + [
                'entity' => 'catalog_product',
                'behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND,
                Import::FIELD_NAME_VALIDATION_STRATEGY =>
                    ProcessingErrorAggregatorInterface::VALIDATION_STRATEGY_STOP_ON_ERROR,
                Import::FIELD_NAME_ALLOWED_ERROR_COUNT => 0,
                Import::FIELD_FIELD_SEPARATOR => ',',
            ]
        );
        $importModel->validateSource($source);
        return $importModel;
    }
}
