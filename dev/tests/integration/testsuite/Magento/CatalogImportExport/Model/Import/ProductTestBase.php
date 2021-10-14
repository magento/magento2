<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Model\Import;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\CatalogImportExport\Model\Import\Product as ImportProduct;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Registry;
use Magento\ImportExport\Helper\Data;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\Import\Source\Csv;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Indexer\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Integration test for \Magento\CatalogImportExport\Model\Import\Product class.
 *
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 * @magentoAppArea adminhtml
 * @magentoDataFixtureBeforeTransaction Magento/Catalog/_files/enable_reindex_schedule.php
 * @magentoDataFixtureBeforeTransaction Magento/Catalog/_files/enable_catalog_product_reindex_schedule.php
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * phpcs:disable Generic.PHP.NoSilencedErrors, Generic.Metrics.NestingLevel, Magento2.Functions.StaticFunction
 */
class ProductTestBase extends TestCase
{
    protected const LONG_FILE_NAME_IMAGE = 'magento_long_image_name_magento_long_image_name_magento_long_image_name.jpg';

    /**
     * @var array
     */
    protected $importedProducts;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product
     */
    protected $_model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var LoggerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $logger;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\CatalogImportExport\Model\Import\Product::class,
            ['logger' => $this->logger]
        );
        $this->importedProducts = [];
        $this->searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);

        parent::setUp();
    }

    /**
     * @inheriDoc
     */
    protected function tearDown(): void
    {
        /* We rollback here the products created during the Import because they were
           created during test execution and we do not have the rollback for them */
        foreach ($this->importedProducts as $productSku) {
            try {
                $product = $this->productRepository->get($productSku, false, null, true);
                $this->productRepository->delete($product);
                // phpcs:disable Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
            } catch (NoSuchEntityException $e) {
                // nothing to delete
            }
        }
    }


    /**
     * @param string $pathToFile
     * @param string $behavior
     * @return \Magento\CatalogImportExport\Model\Import\Product
     */
    protected function createImportModel($pathToFile, $behavior = \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND)
    {
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Framework\Filesystem::class);
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);

        /** @var \Magento\ImportExport\Model\Import\Source\Csv $source */
        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => $pathToFile,
                'directory' => $directory
            ]
        );

        /** @var \Magento\CatalogImportExport\Model\Import\Product $importModel */
        $importModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\CatalogImportExport\Model\Import\Product::class
        );
        $importModel->setParameters(['behavior' => $behavior, 'entity' => 'catalog_product'])->setSource($source);

        return $importModel;
    }

    /**
     * Copy fixture images into media import directory
     */
    public static function mediaImportImageFixture()
    {
        /** @var \Magento\Framework\Filesystem\Directory\Write $varDirectory */
        $varDirectory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\Filesystem::class
        )->getDirectoryWrite(
            DirectoryList::VAR_DIR
        );

        $varDirectory->create('import' . DIRECTORY_SEPARATOR . 'images');
        $dirPath = $varDirectory->getAbsolutePath('import' . DIRECTORY_SEPARATOR . 'images');

        $items = [
            [
                'source' => __DIR__ . '/../../../../Magento/Catalog/_files/magento_image.jpg',
                'dest' => $dirPath . '/magento_image.jpg',
            ],
            [
                'source' => __DIR__ . '/../../../../Magento/Catalog/_files/magento_small_image.jpg',
                'dest' => $dirPath . '/magento_small_image.jpg',
            ],
            [
                'source' => __DIR__ . '/../../../../Magento/Catalog/_files/magento_thumbnail.jpg',
                'dest' => $dirPath . '/magento_thumbnail.jpg',
            ],
            [
                'source' => __DIR__ . '/../../../../Magento/Catalog/_files/' . self::LONG_FILE_NAME_IMAGE,
                'dest' => $dirPath . '/' . self::LONG_FILE_NAME_IMAGE,
            ],
            [
                'source' => __DIR__ . '/_files/magento_additional_image_one.jpg',
                'dest' => $dirPath . '/magento_additional_image_one.jpg',
            ],
            [
                'source' => __DIR__ . '/_files/magento_additional_image_two.jpg',
                'dest' => $dirPath . '/magento_additional_image_two.jpg',
            ],
            [
                'source' => __DIR__ . '/_files/magento_additional_image_three.jpg',
                'dest' => $dirPath . '/magento_additional_image_three.jpg',
            ],
            [
                'source' => __DIR__ . '/_files/magento_additional_image_four.jpg',
                'dest' => $dirPath . '/magento_additional_image_four.jpg',
            ],
        ];

        foreach ($items as $item) {
            copy($item['source'], $item['dest']);
        }
    }

    /**
     * Cleanup media import and catalog directories
     */
    public static function mediaImportImageFixtureRollback()
    {
        $fileSystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\Filesystem::class
        );
        /** @var \Magento\Framework\Filesystem\Directory\Write $mediaDirectory */
        $mediaDirectory = $fileSystem->getDirectoryWrite(DirectoryList::MEDIA);

        /** @var \Magento\Framework\Filesystem\Directory\Write $varDirectory */
        $varDirectory = $fileSystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $varDirectory->delete('import');
        $mediaDirectory->delete('catalog');
    }

    /**
     * Copy incorrect fixture image into media import directory.
     */
    public static function mediaImportImageFixtureError()
    {
        /** @var \Magento\Framework\Filesystem\Directory\Write $varDirectory */
        $varDirectory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\Filesystem::class
        )->getDirectoryWrite(
            DirectoryList::VAR_DIR
        );
        $dirPath = $varDirectory->getAbsolutePath('import' . DIRECTORY_SEPARATOR . 'images');
        $items = [
            [
                'source' => __DIR__ . '/_files/magento_additional_image_error.jpg',
                'dest' => $dirPath . '/magento_additional_image_two.jpg',
            ],
        ];
        foreach ($items as $item) {
            copy($item['source'], $item['dest']);
        }
    }

    /**
     * Export CSV string to array
     *
     * @param string $content
     * @param mixed $entityId
     * @return array
     */
    protected function csvToArray($content, $entityId = null)
    {
        $data = ['header' => [], 'data' => []];

        $lines = str_getcsv($content, "\n");
        foreach ($lines as $index => $line) {
            if ($index == 0) {
                $data['header'] = str_getcsv($line);
            } else {
                $row = array_combine($data['header'], str_getcsv($line));
                if ($entityId !== null && !empty($row[$entityId])) {
                    $data['data'][$row[$entityId]] = $row;
                } else {
                    $data['data'][] = $row;
                }
            }
        }
        return $data;
    }

    /**
     * Import and check data from file.
     *
     * @param string $fileName
     * @param int $expectedErrors
     * @return void
     */
    protected function importDataForMediaTest(string $fileName, int $expectedErrors = 0)
    {
        $filesystem = $this->objectManager->create(\Magento\Framework\Filesystem::class);
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);

        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => __DIR__ . '/_files/' . $fileName,
                'directory' => $directory
            ]
        );
        $this->_model->setParameters(
            [
                'behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND,
                'entity' => 'catalog_product',
                'import_images_file_dir' => 'pub/media/import'
            ]
        );
        $appParams = \Magento\TestFramework\Helper\Bootstrap::getInstance()
            ->getBootstrap()
            ->getApplication()
            ->getInitParams()[Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS];
        $uploader = $this->_model->getUploader();

        $mediaPath = $appParams[DirectoryList::MEDIA][DirectoryList::PATH];
        $varPath = $appParams[DirectoryList::VAR_DIR][DirectoryList::PATH];
        $destDir = $directory->getRelativePath(
            $mediaPath . DIRECTORY_SEPARATOR . 'catalog' . DIRECTORY_SEPARATOR . 'product'
        );
        $tmpDir = $directory->getRelativePath(
            $varPath . DIRECTORY_SEPARATOR . 'import' . DIRECTORY_SEPARATOR . 'images'
        );

        $directory->create($destDir);
        $this->assertTrue($uploader->setDestDir($destDir));
        $this->assertTrue($uploader->setTmpDir($tmpDir));
        $errors = $this->_model->setSource(
            $source
        )->validateData();
        $this->assertTrue($errors->getErrorsCount() == 0);

        $this->_model->importData();
        $this->assertEquals(
            $expectedErrors,
            $this->_model->getErrorAggregator()->getErrorsCount(),
            array_reduce(
                $this->_model->getErrorAggregator()->getAllErrors(),
                function ($output, $error) {
                    return "$output\n{$error->getErrorMessage()}";
                },
                ''
            )
        );
    }

    /**
     * Load product by given product sku
     *
     * @param string $sku
     * @param mixed $store
     * @return \Magento\Catalog\Model\Product
     */
    protected function getProductBySku($sku, $store = null)
    {
        $resource = $this->objectManager->get(\Magento\Catalog\Model\ResourceModel\Product::class);
        $productId = $resource->getIdBySku($sku);
        $product = $this->objectManager->create(\Magento\Catalog\Model\Product::class);
        if ($store) {
            /** @var StoreManagerInterface $storeManager */
            $storeManager = $this->objectManager->get(StoreManagerInterface::class);
            $store = $storeManager->getStore($store);
            $product->setStoreId($store->getId());
        }
        $product->load($productId);

        return $product;
    }

    /**
     * Import file by providing import filename and bunch size.
     *
     * @param string $fileName
     * @param int $bunchSize
     * @return bool
     */
    protected function importFile(string $fileName, int $bunchSize = 100): bool
    {
        $importExportData = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $importExportData->expects($this->atLeastOnce())
            ->method('getBunchSize')
            ->willReturn($bunchSize);
        $this->_model = $this->objectManager->create(
            ImportProduct::class,
            ['importExportData' => $importExportData]
        );
        $filesystem = $this->objectManager->create(Filesystem::class);
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            Csv::class,
            [
                'file' => __DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . $fileName,
                'directory' => $directory,
            ]
        );
        $errors = $this->_model->setParameters(
            [
                'behavior' => Import::BEHAVIOR_APPEND,
                'entity' => 'catalog_product',
                Import::FIELDS_ENCLOSURE => 1,
            ]
        )
            ->setSource($source)
            ->validateData();

        $this->assertTrue($errors->getErrorsCount() === 0);

        return $this->_model->importData();
    }

    /**
     * Set the current admin session user based on a username
     *
     * @param string $username
     */
    protected function loginAdminUserWithUsername(string $username)
    {
        $user = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\User\Model\User::class
        )->loadByUsername($username);

        /** @var $session \Magento\Backend\Model\Auth\Session */
        $session = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Backend\Model\Auth\Session::class
        );
        $session->setUser($user);
    }

    /**
     * Check product request path considering store scope.
     *
     * @param string $storeCode
     * @param string $expected
     * @return void
     */
    protected function assertProductRequestPath($storeCode, $expected)
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var Store $storeCode */
        $store = $objectManager->get(Store::class);
        $storeId = $store->load($storeCode)->getId();

        /** @var Category $category */
        $category = $objectManager->get(Category::class);
        $category->setStoreId($storeId);
        $category->load(555);

        /** @var Registry $registry */
        $registry = $objectManager->get(Registry::class);
        $registry->register('current_category', $category);

        /** @var \Magento\Catalog\Model\Product $product */
        $product = $objectManager->create(\Magento\Catalog\Model\Product::class);
        $id = $product->getIdBySku('product');
        $product->setStoreId($storeId);
        $product->load($id);
        $product->getProductUrl();
        self::assertEquals($expected, $product->getRequestPath());
        $registry->unregister('current_category');
    }

    /**
     * @param int $count
     * @param ProcessingErrorAggregatorInterface $errors
     */
    protected function assertErrorsCount(int $count, ProcessingErrorAggregatorInterface $errors): void
    {
        $this->assertEquals(
            $count,
            $errors->getErrorsCount(),
            array_reduce(
                $errors->getAllErrors(),
                function ($output, $error) {
                    return "$output\n{$error->getErrorMessage()}";
                },
                ''
            )
        );
    }
}
