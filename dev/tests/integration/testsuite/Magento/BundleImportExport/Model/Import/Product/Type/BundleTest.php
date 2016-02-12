<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BundleImportExport\Model\Import\Product\Type;

use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\ImportExport\Model\Import;

/**
 * @magentoAppArea adminhtml
 */
class BundleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Bundle product test Name
     */
    const TEST_PRODUCT_NAME = 'Bundle 1';

    /**
     * Bundle product test Type
     */
    const TEST_PRODUCT_TYPE = 'bundle';

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
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testBundleImport()
    {
        // import data from CSV file
        $pathToFile = __DIR__ . '/../../_files/import_bundle.csv';
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
        //TODO: Uncomment assertion after MAGETWO-49157 fix
        //$this->assertEquals(1, $product->getShipmentType());

        $bundleOptions = $product->getExtensionAttributes()->getBundleProductOptions();
        $this->assertEquals(2, count($bundleOptions));
        foreach ($bundleOptions as $optionKey => $option) {
            $this->assertEquals($optionKey + 1, $option->getData('option_id'));
            $this->assertEquals('checkbox', $option->getData('type'));
            $this->assertEquals('Option ' . ($optionKey + 1), $option->getData('title'));
            $this->assertEquals(self::TEST_PRODUCT_NAME, $option->getData('sku'));
            $this->assertEquals($optionKey + 1, count($option->getData('product_links')));
            foreach ($option->getData('product_links') as $linkKey => $productLink) {
                $this->assertEquals($optionKey + 1 + $linkKey, $productLink->getData('entity_id'));
                $this->assertEquals('Simple ' . ($optionKey + 1 + $linkKey), $productLink->getData('sku'));
                $this->assertEquals($optionKey + 1, $productLink->getData('option_id'));
            }
        }
    }
}
