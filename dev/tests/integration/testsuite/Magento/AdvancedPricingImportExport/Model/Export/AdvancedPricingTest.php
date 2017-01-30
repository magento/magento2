<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedPricingImportExport\Model\Export;

use Magento\Framework\App\Filesystem\DirectoryList;

class AdvancedPricingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\AdvancedPricingImportExport\Model\Export\AdvancedPricing
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

    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->fileSystem = $this->objectManager->get(\Magento\Framework\Filesystem::class);
        $this->model = $this->objectManager->create(
            \Magento\AdvancedPricingImportExport\Model\Export\AdvancedPricing::class
        );
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testExport()
    {
        $productRepository = $this->objectManager->create(
            \Magento\Catalog\Api\ProductRepositoryInterface::class
        );
        $index = 0;
        $ids = [];
        $origPricingData = [];
        while (isset($skus[$index])) {
            $ids[$index] = $productRepository->get($skus[$index])->getId();
            $origPricingData[$index] = $this->objectManager->create(\Magento\Catalog\Model\Product::class)
                ->load($ids[$index])
                ->getTierPrices();
            $index++;
        }

        $csvfile = uniqid('importexport_') . '.csv';

        $this->model->setWriter(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                \Magento\ImportExport\Model\Export\Adapter\Csv::class,
                ['fileSystem' => $this->fileSystem, 'destination' => $csvfile]
            )
        );
        $this->assertNotEmpty($this->model->export());

        /** @var \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing $importModel */
        $importModel = $this->objectManager->create(
            \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::class
        );
        $directory = $this->fileSystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => $csvfile,
                'directory' => $directory
            ]
        );
        $errors = $importModel->setParameters(
            [
                'behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND,
                'entity' => 'advanced_pricing'
            ]
        )->setSource(
            $source
        )->validateData();

        $this->assertTrue(
            $errors->getErrorsCount() == 0,
            'Advanced Pricing import error, imported from file:' . $csvfile
        );
        $importModel->importData();

        while ($index > 0) {
            $index--;
            $newPricingData = $this->objectManager->create(\Magento\Catalog\Model\Product::class)
                ->load($ids[$index])
                ->getTierPrices();
            $this->assertEquals(count($origPricingData[$index]), count($newPricingData));
            $this->assertEqualsOtherThanSkippedAttributes($origPricingData[$index], $newPricingData, []);
        }
    }

    private function assertEqualsOtherThanSkippedAttributes($expected, $actual, $skippedAttributes)
    {
        foreach ($expected as $key => $value) {
            if (in_array($key, $skippedAttributes)) {
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
