<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedImportExport\Model\Import\Product\Type;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\ImportExport\Model\Import;

class GroupedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product
     */
    protected $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->model = $this->objectManager->create('Magento\CatalogImportExport\Model\Import\Product');
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testConfigurableImport()
    {
        // Import data from CSV file
        $pathToFile = __DIR__ . '/../../_files/grouped_product.csv';
        $filesystem = $this->objectManager->create('Magento\Framework\Filesystem');

        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            '\Magento\ImportExport\Model\Import\Source\Csv',
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

        $resource = $this->objectManager->get('Magento\Catalog\Model\ResourceModel\Product');
        $productId = $resource->getIdBySku('Test Grouped');
        $this->assertTrue(is_numeric($productId));
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->objectManager->create('Magento\Catalog\Model\Product');
        $product->load($productId);

        $childProducts = $product->getTypeInstance()->getAssociatedProducts($product);

        $childProductSkus = [];
        foreach ($childProducts as $childProduct) {
            $childProductSkus[] = $childProduct->getSku();
        }

        sort($childProductSkus);

        $this->assertEquals($childProductSkus, ['Simple for Grouped 1', 'Simple for Grouped 2']);
    }
}
