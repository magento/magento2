<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DownloadableImportExport\Model\Import\Product\Type;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * @magentoAppArea adminhtml
 */
class DownloadableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Downloadable product test Name
     */
    const TEST_PRODUCT_NAME = 'Downloadable 1';

    /**
     * Downloadable product test Type
     */
    const TEST_PRODUCT_TYPE = 'downloadable';

    /**
     * Downloadable product Links Group Name
     */
    const TEST_PRODUCT_LINKS_GROUP_NAME = 'TEST Import Links';

    /**
     * Downloadable product Samples Group Name
     */
    const TEST_PRODUCT_SAMPLES_GROUP_NAME = 'TEST Import Samples';

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

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->model = $this->objectManager->create(
            \Magento\CatalogImportExport\Model\Import\Product::class
        );
        /** @var \Magento\Framework\EntityManager\MetadataPool $metadataPool */
        $metadataPool = $this->objectManager->get(\Magento\Framework\EntityManager\MetadataPool::class);
        $this->productMetadata = $metadataPool->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class);
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testDownloadableImport()
    {
        // import data from CSV file
        $pathToFile = __DIR__ . '/../../_files/import_downloadable.csv';
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
        $product = $this->objectManager->create(
            \Magento\Catalog\Model\Product::class
        );
        $product->load($productId);

        $this->assertFalse($product->isObjectNew());
        $this->assertEquals(self::TEST_PRODUCT_NAME, $product->getName());
        $this->assertEquals(self::TEST_PRODUCT_TYPE, $product->getTypeId());

        $downloadableProductLinks   = $product->getExtensionAttributes()->getDownloadableProductLinks();
        $downloadableLinks          = $product->getDownloadableLinks();
        $downloadableProductSamples = $product->getExtensionAttributes()->getDownloadableProductSamples();
        $downloadableSamples        = $product->getDownloadableSamples();

        //TODO: Track Fields: id, link_id, link_file and sample_file)
        $expectedLinks= [
            'file' => [
                'title' => 'TEST Import Link Title File',
                'sort_order' => '78',
                'sample_type' => 'file',
                'price' => '123.0000',
                'number_of_downloads' => '123',
                'is_shareable' => '0',
                'link_type' => 'file'
            ],
            'url'  => [
                'title' => 'TEST Import Link Title URL',
                'sort_order' => '42',
                'sample_type' => 'url',
                'sample_url' => 'http://www.bing.com',
                'price' => '1.0000',
                'number_of_downloads' => '0',
                'is_shareable' => '1',
                'link_type' => 'url',
                'link_url' => 'http://www.google.com'
            ]
        ];
        foreach ($downloadableProductLinks as $link) {
            $actualLink = $link->getData();
            $this->assertArrayHasKey('link_type', $actualLink);
            foreach ($expectedLinks[$actualLink['link_type']] as $expectedKey => $expectedValue) {
                $this->assertArrayHasKey($expectedKey, $actualLink);
                $this->assertEquals($actualLink[$expectedKey], $expectedValue);
            }
        }
        foreach ($downloadableLinks as $link) {
            $actualLink = $link->getData();
            $this->assertArrayHasKey('link_type', $actualLink);
            $this->assertArrayHasKey('product_id', $actualLink);
            $this->assertEquals($actualLink['product_id'], $product->getData($this->productMetadata->getLinkField()));
            foreach ($expectedLinks[$actualLink['link_type']] as $expectedKey => $expectedValue) {
                $this->assertArrayHasKey($expectedKey, $actualLink);
                $this->assertEquals($actualLink[$expectedKey], $expectedValue);
            }
        }

        //TODO: Track Fields: id, sample_id and sample_file)
        $expectedSamples= [
            'file' => [
                'title' => 'TEST Import Sample File',
                'sort_order' => '178',
                'sample_type' => 'file'
            ],
            'url'  => [
                'title' => 'TEST Import Sample URL',
                 'sort_order' => '178',
                 'sample_type' => 'url',
                 'sample_url' => 'http://www.yahoo.com'
            ]
        ];
        foreach ($downloadableProductSamples as $sample) {
            $actualSample = $sample->getData();
            $this->assertArrayHasKey('sample_type', $actualSample);
            foreach ($expectedSamples[$actualSample['sample_type']] as $expectedKey => $expectedValue) {
                $this->assertArrayHasKey($expectedKey, $actualSample);
                $this->assertEquals($actualSample[$expectedKey], $expectedValue);
            }
        }
        foreach ($downloadableSamples as $sample) {
            $actualSample = $sample->getData();
            $this->assertArrayHasKey('sample_type', $actualSample);
            $this->assertArrayHasKey('product_id', $actualSample);
            $this->assertEquals($actualSample['product_id'], $product->getData($this->productMetadata->getLinkField()));
            foreach ($expectedSamples[$actualSample['sample_type']] as $expectedKey => $expectedValue) {
                $this->assertArrayHasKey($expectedKey, $actualSample);
                $this->assertEquals($actualSample[$expectedKey], $expectedValue);
            }
        }
    }
}
