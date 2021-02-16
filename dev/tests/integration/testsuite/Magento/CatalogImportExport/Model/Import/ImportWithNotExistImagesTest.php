<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Model\Import;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogImportExport\Model\Import\ProductImport;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Write;
use Magento\Framework\MessageQueue\MessageEncoder;
use Magento\Framework\ObjectManagerInterface;
use Magento\ImportExport\Model\Export\Consumer;
use Magento\ImportExport\Model\Import as ImportModel;
use Magento\ImportExport\Model\Import\Source\Csv as CsvSource;
use Magento\ImportExport\Model\Import\Source\CsvFactory;
use Magento\MysqlMq\Model\Driver\Queue;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Checks that import with not exist images will fail
 *
 * @see \Magento\CatalogImportExport\Model\Import\Product
 *
 * @magentoAppArea adminhtml
 */
class ImportWithNotExistImagesTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var MessageEncoder */
    private $messageEncoder;

    /** @var Consumer */
    private $consumer;

    /** @var Queue */
    private $queue;

    /** @var Csv */
    private $csvReader;

    /** @var Write */
    private $directory;

    /** @var string */
    private $filePath;

    /** @var ProductImport */
    private $import;

    /** @var CsvFactory */
    private $csvFactory;

    /** @var Filesystem */
    private $fileSystem;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->queue = $this->objectManager->create(Queue::class, ['queueName' => 'export']);
        $this->messageEncoder = $this->objectManager->get(MessageEncoder::class);
        $this->consumer = $this->objectManager->get(Consumer::class);
        $this->directory = $this->objectManager->get(Filesystem::class)->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->csvReader = $this->objectManager->get(Csv::class);
        $this->import = $this->objectManager->get(ProductFactory::class)->create();
        $this->csvFactory = $this->objectManager->get(CsvFactory::class);
        $this->fileSystem = $this->objectManager->get(Filesystem::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        if ($this->filePath && $this->directory->isExist($this->filePath)) {
            $this->directory->delete($this->filePath);
        }

        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/CatalogImportExport/_files/export_queue_product_with_images.php
     *
     * @return void
     */
    public function testImportFailure(): void
    {
        $this->exportProducts();
        $this->assertTrue($this->directory->isExist($this->filePath));
        $csv = $this->csvReader->getData($this->directory->getAbsolutePath($this->filePath));
        $this->assertCount(2, $csv);
        $this->updateExportFile();
        $this->import->setParameters([
            'entity' => Product::ENTITY,
            'behavior' => ImportModel::BEHAVIOR_ADD_UPDATE,
        ]);
        $this->assertImportErrors();
        $this->assertProductNoHaveChanges();
    }

    /**
     * Export products from queue to csv file
     *
     * @return void
     */
    private function exportProducts(): void
    {
        $envelope = $this->queue->dequeue();
        $decodedMessage = $this->messageEncoder->decode('import_export.export', $envelope->getBody());
        $this->consumer->process($decodedMessage);
        $this->filePath = 'export/' . $decodedMessage->getFileName();
    }

    /**
     * Change image names in an export file
     *
     * @return void
     */
    private function updateExportFile(): void
    {
        $absolutePath = $this->directory->getAbsolutePath($this->filePath);
        $csv = $this->csvReader->getData($absolutePath);
        foreach ($csv[1] as $key => $data) {
            if ($data === '/m/a/magento_image.jpg') {
                $csv[1][$key] = '/m/a/invalid_image.jpg';
            }
        }

        $this->csvReader->appendData($absolutePath, $csv);
    }

    /**
     * Get export csv file
     *
     * @param string $file
     * @return CsvSource
     */
    private function prepareFile(string $file): CsvSource
    {
        return $this->csvFactory->create([
            'file' => $file,
            'directory' => $this->fileSystem->getDirectoryWrite(DirectoryList::VAR_DIR),
        ]);
    }

    /**
     * Assert import errors
     *
     * @return void
     */
    private function assertImportErrors(): void
    {
        $errors = $this->import->setSource($this->prepareFile($this->filePath))->validateData();
        $this->assertEmpty($errors->getAllErrors());
        $this->import->importData();
        $this->assertEquals(1, $errors->getErrorsCount());
        $error = $errors->getAllErrors()[0];
        $this->assertEquals('mediaUrlNotAvailable', $error->getErrorCode());
        $errorMsg = (string)__('Imported resource (image) could not be downloaded ' .
            'from external resource due to timeout or access permissions');
        $this->assertEquals($errorMsg, $error->getErrorMessage());
    }

    /**
     * Assert product images were not changed after import
     *
     * @return void
     */
    private function assertProductNoHaveChanges(): void
    {
        $product = $this->productRepository->get('simple');
        $this->assertEquals('/m/a/magento_image.jpg', $product->getImage());
        $this->assertEquals('/m/a/magento_image.jpg', $product->getSmallImage());
        $this->assertEquals('/m/a/magento_image.jpg', $product->getThumbnail());
    }
}
