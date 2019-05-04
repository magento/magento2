<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedImportExport\Model\Import\Product\Type;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\ImportExport\Model\Import;

class GroupedTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Configurable product test Name
     */
    const TEST_PRODUCT_NAME = 'Test Grouped';

    /**
     * Configurable product test Type
     */
    const TEST_PRODUCT_TYPE = 'grouped';

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product
     */
    protected $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Grouped product options SKU list
     *
     * @var array
     */
    protected $optionSkuList = ['Simple for Grouped 1', 'Simple for Grouped 2'];

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
    public function testImport()
    {
        // Import data from CSV file
        $pathToFile = __DIR__ . '/../../_files/grouped_product.csv';
        $filesystem = $this->objectManager->create(\Magento\Framework\Filesystem::class);

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
        $productId = $resource->getIdBySku('Test Grouped');
        $this->assertTrue(is_numeric($productId));
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->objectManager->create(\Magento\Catalog\Model\Product::class);
        $product->load($productId);

        $this->assertFalse($product->isObjectNew());
        $this->assertEquals(self::TEST_PRODUCT_NAME, $product->getName());
        $this->assertEquals(self::TEST_PRODUCT_TYPE, $product->getTypeId());

        $childProductCollection = $product->getTypeInstance()->getAssociatedProducts($product);

        foreach ($childProductCollection as $childProduct) {
            $this->assertContains($childProduct->getSku(), $this->optionSkuList);
        }
    }
}
