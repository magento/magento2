<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Model\Import;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface;
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
use Magento\TestFramework\MysqlMq\DeleteTopicRelatedMessages;
use PHPUnit\Framework\TestCase;

/**
 * Checks import behaviour if specified images do not exist
 *
 * @see \Magento\CatalogImportExport\Model\Import\Product
 *
 * @magentoAppArea adminhtml
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ImportWithNotExistImagesTest extends TestCase
{
    /** @var string */
    private const TOPIC = 'import_export.export';

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
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $objectManager = Bootstrap::getObjectManager();
        /** @var  DeleteTopicRelatedMessages $deleteMessages */
        $deleteMessages = $objectManager->get(DeleteTopicRelatedMessages::class);
        $deleteMessages->execute(self::TOPIC);
    }

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
        $this->csvReader = $this->objectManager->get(Csv::class);
        $this->import = $this->objectManager->get(ProductFactory::class)->create();
        $this->csvFactory = $this->objectManager->get(CsvFactory::class);
        $this->fileSystem = $this->objectManager->get(Filesystem::class);
        $this->directory = $this->fileSystem->getDirectoryWrite(DirectoryList::VAR_IMPORT_EXPORT);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
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
    public function testImportWithUnexistingImages(): void
    {
        $this->exportProducts();
        $this->assertTrue($this->directory->isExist($this->filePath), 'Products were not imported to file');
        $fileContent = $this->getCsvData($this->directory->getAbsolutePath($this->filePath));
        $this->assertCount(2, $fileContent);
        $this->updateFileImagesToInvalidValues();
        $mediaDirectory = $this->fileSystem->getDirectoryWrite(DirectoryList::MEDIA);
        $mediaDirectory->create('import');
        $this->import->setParameters([
            'entity' => Product::ENTITY,
            'behavior' => ImportModel::BEHAVIOR_ADD_UPDATE,
            ImportModel::FIELD_NAME_IMG_FILE_DIR => $mediaDirectory->getAbsolutePath('import')
        ]);
        $this->assertImportErrors();
        $this->assertProductImages('/m/a/magento_image.jpg', 'simple');
    }

    /**
     * Export products from queue to csv file
     *
     * @return void
     */
    private function exportProducts(): void
    {
        $envelope = $this->queue->dequeue();
        $decodedMessage = $this->messageEncoder->decode(self::TOPIC, $envelope->getBody());
        $this->consumer->process($decodedMessage);
        $this->filePath = 'export/' . $decodedMessage->getFileName();
    }

    /**
     * Change image names in an export file
     *
     * @return void
     */
    private function updateFileImagesToInvalidValues(): void
    {
        $absolutePath = $this->directory->getAbsolutePath($this->filePath);
        $csv = $this->getCsvData($absolutePath);
        $imagesKeys = ['base_image', 'small_image', 'thumbnail_image'];
        $imagesPositions = [];
        foreach ($imagesKeys as $key) {
            $imagesPositions[] = array_search($key, $csv[0]);
        }

        foreach ($imagesPositions as $imagesPosition) {
            $csv[1][$imagesPosition] = '/m/a/invalid_image.jpg';
        }

        $this->appendCsvData($absolutePath, $csv);
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
            'directory' => $this->directory,
        ]);
    }

    /**
     * Assert import errors
     *
     * @return void
     */
    private function assertImportErrors(): void
    {
        $validationErrors = $this->import->setSource($this->prepareFile($this->filePath))->validateData();
        $this->assertEmpty($validationErrors->getAllErrors());
        $this->import->getErrorAggregator()->clear();
        $this->import->importData();
        $importErrors = $this->import->getErrorAggregator()->getAllErrors();
        $this->assertCount(1, $importErrors);
        $importError = reset($importErrors);
        $this->assertEquals(
            RowValidatorInterface::ERROR_MEDIA_URL_NOT_ACCESSIBLE,
            $importError->getErrorCode()
        );
        $errorMsg = (string)__('Imported resource (image) could not be downloaded ' .
            'from external resource due to timeout or access permissions');
        $this->assertEquals($errorMsg, $importError->getErrorMessage());
    }

    /**
     * Assert product images were not changed after import
     *
     * @param string $imageName
     * @param string $productSku
     * @return void
     */
    private function assertProductImages(string $imageName, string $productSku): void
    {
        $product = $this->productRepository->get($productSku);
        $this->assertEquals($imageName, $product->getImage());
        $this->assertEquals($imageName, $product->getSmallImage());
        $this->assertEquals($imageName, $product->getThumbnail());
    }

    /**
     * Parse csv file and return csv data as array
     *
     * @param string $filePath
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function getCsvData(string $filePath): array
    {
        $driver = $this->directory->getDriver();
        $fileResource = $driver->fileOpen($filePath, 'r');

        $data = [];
        while ($rowData = $driver->fileGetCsv($fileResource, 100000)) {
            $data[] = $rowData;
        }
        $driver->fileClose($fileResource);

        return $data;
    }

    /**
     * Appends csv data to the file
     *
     * @param string $filePath
     * @param array $csv
     * @return void
     */
    private function appendCsvData(string $filePath, array $csv): void
    {
        $driver = $this->directory->getDriver();
        $fileResource = $driver->fileOpen($filePath, 'w');

        foreach ($csv as $dataRow) {
            $driver->filePutCsv($fileResource, $dataRow);
        }
        $driver->fileClose($fileResource);
    }
}
