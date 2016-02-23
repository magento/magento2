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
     * @var \Magento\Framework\Filesystem
     */
    protected $fileSystem;

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
        $this->fileSystem = $this->objectManager->get('Magento\Framework\Filesystem');
        $this->model = $this->objectManager->create(
            'Magento\CatalogImportExport\Model\Export\Product'
        );
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @dataProvider exportDataProvider
     */
    public function testExport($fixture, $skus)
    {
        $fixturePath = $this->fileSystem->getDirectoryRead(DirectoryList::ROOT)
            ->getAbsolutePath('/dev/tests/integration/testsuite/' . $fixture);
        include $fixturePath;

        $this->executeExportTest($skus);
    }

    protected function executeExportTest($skus)
    {
        $productRepository = $this->objectManager->create(
            'Magento\Catalog\Api\ProductRepositoryInterface'
        );
        $index = 0;
        $ids = [];
        $origProductData = [];
        while (isset($skus[$index])) {
            $ids[$index] = $productRepository->get($skus[$index])->getId();
            $origProductData[$index] = $this->objectManager->create('Magento\Catalog\Model\Product')->load($ids[$index])->getData();
            $index++;
        }

        $csvfile = uniqid('importexport_') . '.csv';

        $this->model->setWriter(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                'Magento\ImportExport\Model\Export\Adapter\Csv',
                ['fileSystem' => $this->fileSystem, 'destination' => $csvfile]
            )
        );
        $this->assertNotEmpty($this->model->export());

        /** @var \Magento\CatalogImportExport\Model\Import\Product $importModel */
        $importModel = $this->objectManager->create(
            'Magento\CatalogImportExport\Model\Import\Product'
        );
        $directory = $this->fileSystem->getDirectoryWrite(DirectoryList::VAR_DIR);
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

        $this->assertTrue($errors->getErrorsCount() == 0, 'Product import error, imported from file:' . $csvfile);
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
