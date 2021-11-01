<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Model\Import;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product as ProductEntity;
use Magento\Catalog\Model\Product\Media\ConfigInterface;
use Magento\Framework\App\Bootstrap as AppBootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\ObjectManagerInterface;
use Magento\ImportExport\Model\Import;
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
class ImportWithSharedImagesTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Filesystem */
    private $fileSystem;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var Filesystem\Directory\Write */
    private $mediaDirectory;

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

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->fileSystem = $this->objectManager->get(Filesystem::class);
        $this->mediaDirectory = $this->fileSystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->mediaConfig = $this->objectManager->get(ConfigInterface::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
        $this->import = $this->objectManager->get(ProductFactory::class)->create();
        $this->csvFactory = $this->objectManager->get(CsvFactory::class);
        $this->importDataResource = $this->objectManager->get(Data::class);
        $this->appParams = Bootstrap::getInstance()->getBootstrap()->getApplication()
            ->getInitParams()[AppBootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS];
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
     */
    public function testImportProductsWithSameImages(): void
    {
        $this->moveImages('magento_image.jpg');
        $source = $this->prepareFile('catalog_import_products_with_same_images.csv');
        $this->updateUploader();
        $errors = $this->import->setParameters([
            'behavior' => Import::BEHAVIOR_ADD_UPDATE,
            'entity' => ProductEntity::ENTITY,
        ])
            ->setSource($source)->validateData();
        $this->assertEmpty($errors->getAllErrors());
        $this->import->importData();
        $this->createdProductsSkus = ['SimpleProductForTest1', 'SimpleProductForTest2'];
        $this->checkProductsImages('/m/a/magento_image.jpg', $this->createdProductsSkus);
    }

    /**
     * Check product images
     *
     * @param string $expectedImagePath
     * @param array $productSkus
     * @return void
     */
    private function checkProductsImages(string $expectedImagePath, array $productSkus): void
    {
        foreach ($productSkus as $productSku) {
            $product = $this->productRepository->get($productSku);
            $productImageItem = $product->getMediaGalleryImages()->getFirstItem();
            $productImageFile = $productImageItem->getFile();
            $productImagePath = $productImageItem->getPath();
            $this->filesToRemove[] = $productImagePath;
            $this->assertEquals($expectedImagePath, $productImageFile);
            $this->assertNotEmpty($productImagePath);
            $this->assertTrue($this->mediaDirectory->isExist($productImagePath));
        }
    }

    /**
     * Remove created files
     *
     * @return void
     */
    private function removeFiles(): void
    {
        foreach ($this->filesToRemove as $file) {
            $this->mediaDirectory->delete($file);
        }
    }

    /**
     * Remove created products
     *
     * @return void
     */
    private function removeProducts(): void
    {
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
        $fixtureDir = realpath(__DIR__ . '/../../_files');
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
        $mediaDir = !$this->mediaDirectory->getDriver() instanceof Filesystem\Driver\File ?
            'media' : $this->appParams[DirectoryList::MEDIA][DirectoryList::PATH];

        $destDir = $mediaDir . DIRECTORY_SEPARATOR . $this->mediaConfig->getBaseMediaPath();
        $tmpDir = $mediaDir . DIRECTORY_SEPARATOR . 'import/images';

        $this->mediaDirectory->create($this->mediaConfig->getBaseMediaPath());
        $this->mediaDirectory->create('import/images');

        $this->import->setParameters(
            [
                Import::FIELD_NAME_IMG_FILE_DIR => $mediaDir . '/import/images'
            ]
        );
        $uploader = $this->import->getUploader();
        $uploader->setDestDir($destDir);
        $uploader->setTmpDir($tmpDir);
    }

    /**
     * Move images to appropriate folder
     *
     * @param string $fileName
     * @return void
     */
    private function moveImages(string $fileName): void
    {
        $tmpDir = $this->mediaDirectory->getRelativePath('import/images');
        $fixtureDir = realpath(__DIR__ . '/../../_files');
        $tmpFilePath = $this->mediaDirectory->getAbsolutePath($tmpDir . DIRECTORY_SEPARATOR . $fileName);
        $this->mediaDirectory->create($tmpDir);
        $this->mediaDirectory->getDriver()->filePutContents(
            $tmpFilePath,
            file_get_contents($fixtureDir . DIRECTORY_SEPARATOR . $fileName)
        );
        $this->filesToRemove[] = $tmpFilePath;
    }
}
