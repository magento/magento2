<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Export;
use Magento\Framework\App\Filesystem\DirectoryList;

class AbstractProductExportTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogImportExport\Model\Export\Product
     */
    protected $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * skipped attributes
     *
     * @var array
     */
    public static $skippedAttributes = [
        'options',
        'updated_at',
        'extension_attributes',
        'category_ids',
    ];

    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->model = $this->objectManager->create(
            'Magento\CatalogImportExport\Model\Export\Product'
        );
    }

    protected function executeExportTest($productNames)
    {
        $productRepository = $this->objectManager->create(
            'Magento\Catalog\Api\ProductRepositoryInterface'
        );
        $index = 0;
        $ids = [];
        $origProductData = [];
        while (isset($productNames[$index])) {
            $ids[$index] = $productRepository->get($productNames[$index])->getId();
            $origProductData[$index] = $this->objectManager->create('Magento\Catalog\Model\Product')->load($ids[$index])->getData();
            $index++;
        }

        $fileSystem = $this->objectManager->get('Magento\Framework\Filesystem');
        $csvfile = uniqid('importexport_');

        $this->model->setWriter(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                'Magento\ImportExport\Model\Export\Adapter\Csv',
                ['fileSystem' => $fileSystem, 'destination' => $csvfile]
            )
        );
        $this->assertNotEmpty($this->model->export());

        /** @var \Magento\CatalogImportExport\Model\Import\Product $importModel */
        $importModel = $this->objectManager->create(
            'Magento\CatalogImportExport\Model\Import\Product'
        );
        $directory = $fileSystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $source = $this->objectManager->create(
            '\Magento\ImportExport\Model\Import\Source\Csv',
            [
                'file' => $csvfile,
                'directory' => $directory
            ]
        );
        $errors = $importModel->setParameters(
            ['behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND, 'entity' => 'catalog_product']
        )->setSource(
            $source
        )->validateData();

        $this->assertTrue($errors->getErrorsCount() == 0);
        $importModel->importData();

        while ($index > 0) {
            $index--;
            $newProductData = $this->objectManager->create('Magento\Catalog\Model\Product')->load($ids[$index])->getData();
            $this->assertEquals(count($origProductData[$index]), count($newProductData));
            $this->assertEqualsOtherThanUpdatedAt($origProductData[$index], $newProductData);
        }
    }

    private function assertEqualsOtherThanUpdatedAt($expected, $actual)
    {
        foreach ($expected as $key => $value) {
            if (in_array($key, self::$skippedAttributes)) {
                continue;
            } else {
                $this->assertEquals(
                    $value,
                    $actual[$key],
                    'Assert value at key - ' . $key . ' failed'
                );
            }
        }
    }
}
