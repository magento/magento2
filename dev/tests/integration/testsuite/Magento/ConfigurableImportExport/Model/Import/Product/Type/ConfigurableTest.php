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
 * @magentoAppArea adminhtml
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigurableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Configurable product test Name
     */
    const TEST_PRODUCT_NAME = 'Configurable 1';

    /**
     * Configurable product test Type
     */
    const TEST_PRODUCT_TYPE = 'configurable';

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product
     */
    protected $model;

    /**
     * Configurable product options SKU list
     *
     * @var array
     */
    protected $optionSkuList = ['Configurable 1-Option 2-Option 1'];

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType::$commonAttributesCache = [];
        $this->model = $this->objectManager->create(\Magento\CatalogImportExport\Model\Import\Product::class);
    }

    /**
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

        $this->assertFalse($product->isObjectNew());
        $this->assertEquals(self::TEST_PRODUCT_NAME, $product->getName());
        $this->assertEquals(self::TEST_PRODUCT_TYPE, $product->getTypeId());

        $optionCollection = $product->getTypeInstance()->getConfigurableOptions($product);
        foreach ($optionCollection as $option) {
            $this->assertContains($option[0]['sku'], $this->optionSkuList);
        }

        $optionIdList = $resource->getProductsIdsBySkus($this->optionSkuList);
        foreach ($optionIdList as $optionId) {
            $this->assertArrayHasKey($optionId, $product->getExtensionAttributes()->getConfigurableProductLinks());
        }

        $this->assertEquals(2, count($optionCollection));

        $attributesPositionExpectation = [
            'test_attribute_2' => 0,
            'test_attribute_1' => 1,
        ];

        /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute $configurableProductOption */
        foreach ($configurableProductOptions as $configurableProductOption) {
            $productAttribute = $configurableProductOption->getProductAttribute();
            $productAttributeCode = $productAttribute->getAttributeCode();
            $productAttributeData = $productAttribute->getData();
            $productAttributeDataExpectation = self::getProductAttributesDataExpectation()[$productAttributeCode];

            $productOptionData = $configurableProductOption->getData();
            $productOptionLabel = $productOptionData['label'];
            $productOptionsDataExpectation = self::getProductOptionsDataExpectation()[$productOptionLabel];

            if (isset($attributesPositionExpectation[$productAttributeCode])) {
                $expectedPosition = $attributesPositionExpectation[$productAttributeCode];
                $this->assertEquals($expectedPosition, $configurableProductOption->getPosition());
            }

            $this->assertArrayHasKey('product_super_attribute_id', $productOptionData);
            $this->assertArrayHasKey('product_id', $productOptionData);
            $this->assertArrayHasKey('attribute_id', $productOptionData);
            $this->assertArrayHasKey('position', $productOptionData);
            $this->assertArrayHasKey('extension_attributes', $productOptionData);
            $this->assertArrayHasKey('product_attribute', $productOptionData);
            $this->assertArrayHasKey('attribute_id', $productAttributeData);
            $this->assertArrayHasKey('entity_type_id', $productAttributeData);
            $this->assertArrayHasKey('attribute_code', $productAttributeData);
            $this->assertArrayHasKey('frontend_label', $productAttributeData);
            $this->assertArrayHasKey('label', $productOptionData);
            $this->assertArrayHasKey('use_default', $productOptionData);
            $this->assertArrayHasKey('options', $productOptionData);

            $this->assertEquals(
                $productAttributeDataExpectation['attribute_code'],
                $productAttributeData['attribute_code']
            );
            $this->assertEquals(
                $product->getData('entity_id'),
                $productOptionData['product_id']);
            $this->assertEquals(
                $productAttributeDataExpectation['frontend_label'],
                $productAttributeData['frontend_label']
            );
            $this->assertEquals(
                $productOptionsDataExpectation['label'],
                $productOptionData['label']
            );
            $this->assertEquals(
                $productOptionsDataExpectation['options']['label'],
                $productOptionData['options'][0]['label']
            );
            $this->assertEquals(
                $productOptionsDataExpectation['options']['default_label'],
                $productOptionData['options'][0]['default_label']
            );
            $this->assertEquals(
                $productOptionsDataExpectation['options']['store_label'],
                $productOptionData['options'][0]['store_label']
            );
            $this->assertArrayHasKey('values', $productOptionData);
            $valuesData = $productOptionData['values'];
            $this->assertEquals(1, count($valuesData));
        }
    }

    /**
     * @return array
     */
    private function  getProductOptionsDataExpectation()
    {
        return [
            'Test attribute 1' => [
                'label' => 'Test attribute 1',
                'options' => [
                    'label' => 'Option 1',
                    'default_label' => 'Option 1',
                    'store_label' => 'Option 1'
                ]
            ],
            'Test attribute 2' => [
                'label' => 'Test attribute 2',
                'options' => [
                    'label' => 'Option 2',
                    'default_label' => 'Option 2',
                    'store_label' => 'Option 2'
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    private function getProductAttributesDataExpectation()
    {
        return [
            'test_attribute_1' => [
                'attribute_code' => 'test_attribute_1',
                'frontend_label' => 'Test attribute 1'
            ],
            'test_attribute_2' => [
                'attribute_code' => 'test_attribute_2',
                'frontend_label' => 'Test attribute 2'
            ]
        ];
    }
}
