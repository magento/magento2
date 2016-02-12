<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableImportExport\Model\Import\Product\Type;

use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\ImportExport\Model\Import;

/**
 * @magentoAppArea adminhtml
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
    protected $_model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\CatalogImportExport\Model\Import\Product'
        );
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_attribute.php
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testConfigurableImport()
    {
        // import data from CSV file
        $pathToFile = __DIR__ . '/../../_files/import_configurable.csv';
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\Filesystem'
        );

        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            '\Magento\ImportExport\Model\Import\Source\Csv',
            [
                'file' => $pathToFile,
                'directory' => $directory
            ]
        );
        $errors = $this->_model->setSource(
            $source
        )->setParameters(
            [
                'behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND,
                'entity' => 'catalog_product'
            ]
        )->validateData();

        $this->assertTrue($errors->getErrorsCount() == 0);
        $this->_model->importData();

        $resource = $this->objectManager->get('Magento\Catalog\Model\ResourceModel\Product');
        $productId = $resource->getIdBySku(self::TEST_PRODUCT_NAME);
        $this->assertTrue(is_numeric($productId));
        /** @var \Magento\Catalog\Model\Product $product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product->load($productId);

        $this->assertFalse($product->isObjectNew());
        $this->assertEquals(self::TEST_PRODUCT_NAME, $product->getName());
        $this->assertEquals(self::TEST_PRODUCT_TYPE, $product->getTypeId());
        $this->assertEquals([1 => "1", 2 => "2"], $product->getExtensionAttributes()->getConfigurableProductLinks());
        $configurableOptions = $product->getExtensionAttributes()->getConfigurableProductOptions();
        $this->assertEquals(1, count($configurableOptions));
        foreach ($configurableOptions as $option) {
            $optionData = $option->getData();
            $this->assertArrayHasKey('product_super_attribute_id', $optionData);
            $this->assertArrayHasKey('product_id', $optionData);
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
}
