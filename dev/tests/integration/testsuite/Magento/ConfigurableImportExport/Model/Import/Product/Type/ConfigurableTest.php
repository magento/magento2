<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableImportExport\Model\Import\Product\Type;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\ImportExport\Model\Import;

/**
 * Product type configurable import test.
 *
 * @magentoAppArea adminhtml
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigurableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Configurable product test Type
     */
    const TEST_PRODUCT_TYPE = 'configurable';

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product
     */
    protected $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\EntityManager\EntityMetadata
     */
    protected $productMetadata;

    /**
     * Configurable product test Name.
     */
    const TEST_PRODUCT_NAME = 'Configurable 1';

    /**
     * Configurable product options SKU list
     *
     * @var array
     */
    protected $optionSkuList = ['Configurable 1-Option 2-Option 1'];

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType::$commonAttributesCache = [];
        $this->model = $this->objectManager->create(\Magento\CatalogImportExport\Model\Import\Product::class);
        /** @var \Magento\Framework\EntityManager\MetadataPool $metadataPool */
        $metadataPool = $this->objectManager->get(\Magento\Framework\EntityManager\MetadataPool::class);
        $this->productMetadata = $metadataPool->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class);
    }

    /**
     * @return array
     */
    public function configurableImportDataProvider()
    {
        return [
            'Configurable 1' => [
                __DIR__ . '/../../_files/import_configurable.csv',
                'Configurable 1',
                ['Configurable 1-Option 1', 'Configurable 1-Option 2'],
            ],
            '12345' => [
                __DIR__ . '/../../_files/import_configurable_12345.csv',
                '12345',
                ['Configurable 1-Option 1', 'Configurable 1-Option 2'],
            ],
        ];
    }

    /**
     * @param string $pathToFile Path to import file
     * @param string $productName Name/sku of configurable product
     * @param array $optionSkuList Name of variations for configurable product
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_attribute.php
     * @magentoAppArea adminhtml
     * @dataProvider configurableImportDataProvider
     */
    public function testConfigurableImport($pathToFile, $productName, $optionSkuList)
    {
        // import data from CSV file
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
                'entity' => 'catalog_product'
            ]
        )->validateData();

        $this->assertTrue($errors->getErrorsCount() == 0);
        $this->model->importData();

        /** @var \Magento\Catalog\Model\ResourceModel\Product $resource */
        $resource = $this->objectManager->get(\Magento\Catalog\Model\ResourceModel\Product::class);
        $productId = $resource->getIdBySku($productName);
        $this->assertTrue(is_numeric($productId));
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->objectManager->get(ProductRepositoryInterface::class)->getById($productId);

        $this->assertFalse($product->isObjectNew());
        $this->assertEquals($productName, $product->getName());
        $this->assertEquals(self::TEST_PRODUCT_TYPE, $product->getTypeId());

        $optionCollection = $product->getTypeInstance()->getConfigurableOptions($product);
        foreach ($optionCollection as $option) {
            foreach ($option as $optionData) {
                $this->assertContains($optionData['sku'], $optionSkuList);
            }
        }

        $optionIdList = $resource->getProductsIdsBySkus($optionSkuList);
        foreach ($optionIdList as $optionId) {
            $this->assertArrayHasKey($optionId, $product->getExtensionAttributes()->getConfigurableProductLinks());
        }

        $configurableOptionCollection = $product->getExtensionAttributes()->getConfigurableProductOptions();
        $this->assertEquals(1, count($configurableOptionCollection));
        foreach ($configurableOptionCollection as $option) {
            $optionData = $option->getData();
            $this->assertArrayHasKey('product_super_attribute_id', $optionData);
            $this->assertArrayHasKey('product_id', $optionData);
            $this->assertEquals($product->getData($this->productMetadata->getLinkField()), $optionData['product_id']);
            $this->assertArrayHasKey('attribute_id', $optionData);
            $this->assertArrayHasKey('position', $optionData);
            $this->assertArrayHasKey('extension_attributes', $optionData);
            $this->assertArrayHasKey('product_attribute', $optionData);
            $productAttributeData = $optionData['product_attribute']->getData();
            $this->assertArrayHasKey('attribute_id', $productAttributeData);
            $this->assertArrayHasKey('entity_type_id', $productAttributeData);
            $this->assertArrayHasKey('attribute_code', $productAttributeData);
            $this->assertEquals('test_configurable', $productAttributeData['attribute_code']);
            $this->assertArrayHasKey('frontend_label', $productAttributeData);
            $this->assertEquals('Test Configurable', $productAttributeData['frontend_label']);
            $this->assertArrayHasKey('label', $optionData);
            $this->assertEquals('test_configurable', $optionData['label']);
            $this->assertArrayHasKey('use_default', $optionData);
            $this->assertArrayHasKey('options', $optionData);
            $this->assertEquals('Option 1', $optionData['options'][0]['label']);
            $this->assertEquals('Option 1', $optionData['options'][0]['default_label']);
            $this->assertEquals('Option 1', $optionData['options'][0]['store_label']);
            $this->assertEquals('Option 2', $optionData['options'][1]['label']);
            $this->assertEquals('Option 2', $optionData['options'][1]['default_label']);
            $this->assertEquals('Option 2', $optionData['options'][1]['store_label']);
            $this->assertArrayHasKey('values', $optionData);
            $valuesData = $optionData['values'];
            $this->assertEquals(2, count($valuesData));
        }
    }

    /**
     * Tests that after import configurable products super attributes retain ordering.
     *
     * @magentoDataFixture Magento/ConfigurableImportExport/Model/Import/_files/configurable_attributes.php
     * @magentoAppArea adminhtml
     */
    public function testConfigurableWithAttributesSortingImport()
    {
        // import data from CSV file
        $pathToFile = __DIR__ . '/../../_files/import_configurable_with_attributes_sorting.csv';
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
        $errors = $this->model->setSource($source)
            ->setParameters(
                [
                    'behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND,
                    'entity' => 'catalog_product'
                ]
            )
            ->validateData();

        $this->assertTrue($errors->getErrorsCount() == 0);
        $this->model->importData();

        /** @var \Magento\Catalog\Model\ResourceModel\Product $resource */
        $resource = $this->objectManager->get(\Magento\Catalog\Model\ResourceModel\Product::class);
        $productId = $resource->getIdBySku(self::TEST_PRODUCT_NAME);
        $this->assertTrue(is_numeric($productId));
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->objectManager->get(ProductRepositoryInterface::class)->getById($productId);
        $configurableProductOptions = $product->getExtensionAttributes()->getConfigurableProductOptions();

        $attributesPositionExpectation = [
            'test_attribute_2' => 0,
            'test_attribute_1' => 1,
        ];

        /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute $configurableProductOption */
        foreach ($configurableProductOptions as $configurableProductOption) {
            $productAttribute = $configurableProductOption->getProductAttribute();
            $productAttributeCode = $productAttribute->getAttributeCode();

            if (isset($attributesPositionExpectation[$productAttributeCode])) {
                $expectedPosition = $attributesPositionExpectation[$productAttributeCode];
                $this->assertEquals($expectedPosition, $configurableProductOption->getPosition());
            }
        }
    }
}
