<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
    protected $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * List of Bundle options SKU
     *
     * @var array
     */
    protected $optionSkuList = ['Simple 1', 'Simple 2', 'Simple 3'];

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->model = $this->objectManager->create(\Magento\CatalogImportExport\Model\Import\Product::class);
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

        $resource = $this->objectManager->get(\Magento\Catalog\Model\ResourceModel\Product::class);
        $productId = $resource->getIdBySku(self::TEST_PRODUCT_NAME);
        $this->assertTrue(is_numeric($productId));
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->objectManager->create(\Magento\Catalog\Model\Product::class);
        $product->load($productId);

        $this->assertFalse($product->isObjectNew());
        $this->assertEquals(self::TEST_PRODUCT_NAME, $product->getName());
        $this->assertEquals(self::TEST_PRODUCT_TYPE, $product->getTypeId());
        $this->assertEquals(1, $product->getShipmentType());

        $optionIdList = $resource->getProductsIdsBySkus($this->optionSkuList);
        $bundleOptionCollection = $product->getExtensionAttributes()->getBundleProductOptions();
        $this->assertEquals(2, count($bundleOptionCollection));
        foreach ($bundleOptionCollection as $optionKey => $option) {
            $this->assertEquals('checkbox', $option->getData('type'));
            $this->assertEquals('Option ' . ($optionKey + 1), $option->getData('title'));
            $this->assertEquals(self::TEST_PRODUCT_NAME, $option->getData('sku'));
            $this->assertEquals($optionKey + 1, count($option->getData('product_links')));
            foreach ($option->getData('product_links') as $linkKey => $productLink) {
                $optionSku = 'Simple ' . ($optionKey + 1 + $linkKey);
                $this->assertEquals($optionIdList[$optionSku], $productLink->getData('entity_id'));
                $this->assertEquals($optionSku, $productLink->getData('sku'));
            }
        }
    }
}
