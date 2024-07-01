<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Model\Category\Plugin;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product as ProductEntity;
use Magento\Catalog\Model\Product\Media\ConfigInterface;
use Magento\Framework\App\Bootstrap as AppBootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Write;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\ObjectManagerInterface;
use Magento\ImportExport\Model\Import;
use Magento\CatalogImportExport\Model\Import\ProductFactory;
use Magento\ImportExport\Model\Import\Source\Csv;
use Magento\ImportExport\Model\Import\Source\CsvFactory;
use Magento\ImportExport\Model\ResourceModel\Import\Data;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Checks that product import with same images can be successfully done
 *
 * @magentoAppArea adminhtml
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StorageTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Filesystem */
    private $fileSystem;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var File */
    private $fileDriver;

    /** @var Import */
    private $import;

    /** @var ConfigInterface */
    private $mediaConfig;

    /** @var array */
    private $appParams;

    /** @var array */
    private $createdProductsSkus = [];

    /** @var array */
    private $filesToRemove = [];

    /** @var CsvFactory */
    private $csvFactory;

    /** @var Data */
    private $importDataResource;

    /** @var Write */
    private $mediaDirectory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->fileSystem = $this->objectManager->get(Filesystem::class);
        $this->fileDriver = $this->objectManager->get(File::class);
        $this->mediaConfig = $this->objectManager->get(ConfigInterface::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
        $this->import = $this->objectManager->get(ProductFactory::class)->create();
        $this->csvFactory = $this->objectManager->get(CsvFactory::class);
        $this->importDataResource = $this->objectManager->get(Data::class);
        $this->appParams = Bootstrap::getInstance()->getBootstrap()->getApplication()
            ->getInitParams()[AppBootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS];
        $this->mediaDirectory = $this->fileSystem->getDirectoryWrite(DirectoryList::MEDIA);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->removeFiles();
        $this->removeProducts();
        $this->importDataResource->cleanBunches();

        parent::tearDown();
    }

    /**
     * @return void
     * @magentoDataFixture Magento/CatalogUrlRewrite/_files/issue34210_structure.php
     * @magentoDbIsolation disabled
     */
    public function testIssue34210(): void
    {
        $source = $this->prepareFile('issue34210.csv');
        $this->updateUploader();
        $errors = $this->import->setParameters([
            'behavior' => Import::BEHAVIOR_ADD_UPDATE,
            'entity' => ProductEntity::ENTITY,
        ])
            ->setSource($source)->validateData();
        $this->assertEmpty($errors->getAllErrors());

        $result = $this->import->importData();
        $this->assertTrue($result);
    }

    /**
     * Remove created files
     *
     * @return void
     */
    private function removeFiles(): void
    {
        foreach ($this->filesToRemove as $file) {
            if ($this->fileDriver->isExists($file)) {
                $this->fileDriver->deleteFile($file);
            }
        }
    }

    /**
     * Remove created products
     *
     * @return void
     */
    private function removeProducts(): void
    {
        $resource = $this->objectManager->get(ResourceConnection::class);
        $connection = $resource->getConnection();
        $connection->delete('catalog_category_product');
        foreach ($this->createdProductsSkus as $sku) {
            try {
                $this->productRepository->deleteById($sku);
            } catch (NoSuchEntityException $e) {
                //already removed
            }
        }
    }

    /**
     * Prepare file
     *
     * @param string $fileName
     * @return Csv
     */
    private function prepareFile(string $fileName): Csv
    {
        $tmpDirectory = $this->fileSystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $fixtureDir = realpath(__DIR__ . '/../../../_files');
        $filePath = $tmpDirectory->getAbsolutePath($fileName);
        $this->filesToRemove[] = $filePath;
        $tmpDirectory->getDriver()->copy($fixtureDir . DIRECTORY_SEPARATOR . $fileName, $filePath);
        $source = $this->csvFactory->create(
            [
                'file' => $fileName,
                'directory' => $tmpDirectory
            ]
        );

        return $source;
    }

    /**
     * Update upload to use sandbox folders
     *
     * @return void
     */
    private function updateUploader(): void
    {
        $mediaDir = !$this->mediaDirectory->getDriver() instanceof File ?
            DirectoryList::MEDIA : $this->appParams[DirectoryList::MEDIA][DirectoryList::PATH];
        $destDir = $mediaDir . DIRECTORY_SEPARATOR . $this->mediaConfig->getBaseMediaPath();
        $tmpDir =  $mediaDir . DIRECTORY_SEPARATOR . 'import';
        $this->mediaDirectory->create($this->mediaConfig->getBaseMediaPath());
        $this->mediaDirectory->create('import');
        $this->import->setParameters([Import::FIELD_NAME_IMG_FILE_DIR => $tmpDir]);
        $uploader = $this->import->getUploader();
        $uploader->setDestDir($destDir);
        $uploader->setTmpDir($tmpDir);
    }
}
