<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Cms\Controller\Adminhtml\Wysiwyg\Images;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Directory\DenyListPathValidator;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\Driver\File;
use Magento\RemoteStorage\Driver\DriverPool;
use Magento\RemoteStorage\Model\Filesystem\Directory\WriteFactory;

/**
 * Test for \Magento\Cms\Controller\Adminhtml\Wysiwyg\Images\DeleteFiles class.
 *
 * @magentoAppArea adminhtml
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DeleteFilesTest extends \PHPUnit\Framework\TestCase
{
    private const MEDIA_GALLERY_IMAGE_FOLDERS_CONFIG_PATH
        = 'system/media_storage_configuration/allowed_resources/media_gallery_image_folders';

    /**
     * @var array
     */
    private $origConfigValue;

    /**
     * @var \Magento\Cms\Controller\Adminhtml\Wysiwyg\Images\DeleteFiles
     */
    private $model;

    /**
     * @var  \Magento\Cms\Helper\Wysiwyg\Images
     */
    private $imagesHelper;

    /**
     * @var WriteInterface
     */
    private $mediaDirectory;

    /**
     * @var string
     */
    private $fullDirectoryPath;

    /**
     * @var string
     */
    private $fileName = 'magento_small_image.jpg';

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var WriteInterface
     */
    private $bypassDenyListWrite;

    /**
     * @inheritdoc
     * @throws FileSystemException
     */
    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $directoryName = 'testDir';
        $this->directoryList = $this->objectManager->get(DirectoryList::class);
        $this->filesystem = $this->objectManager->get(\Magento\Framework\Filesystem::class);
        /** @var \Magento\Cms\Helper\Wysiwyg\Images $imagesHelper */
        $this->imagesHelper = $this->objectManager->get(\Magento\Cms\Helper\Wysiwyg\Images::class);
        $this->mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->fullDirectoryPath = $this->imagesHelper->getStorageRoot() . $directoryName;
        $this->mediaDirectory->create($this->mediaDirectory->getRelativePath($this->fullDirectoryPath));
        $filePath =  $this->fullDirectoryPath . DIRECTORY_SEPARATOR . $this->fileName;
        $fixtureDir = realpath(__DIR__ . '/../../../../../Catalog/_files');
        $this->copyFile($fixtureDir . '/' . $this->fileName, $filePath);
        $this->model = $this->objectManager->get(\Magento\Cms\Controller\Adminhtml\Wysiwyg\Images\DeleteFiles::class);
        $config = $this->objectManager->get(ScopeConfigInterface::class);
        $this->origConfigValue = $config->getValue(
            self::MEDIA_GALLERY_IMAGE_FOLDERS_CONFIG_PATH,
            'default'
        );
        $scopeConfig = $this->objectManager->get(\Magento\Framework\App\Config\MutableScopeConfigInterface::class);
        $scopeConfig->setValue(
            self::MEDIA_GALLERY_IMAGE_FOLDERS_CONFIG_PATH,
            array_merge($this->origConfigValue, ['testDir']),
        );
    }

    protected function tearDown(): void
    {
        $this->mediaDirectory->delete($this->mediaDirectory->getRelativePath($this->fullDirectoryPath));
        $this->mediaDirectory->delete('secondDir');
        $scopeConfig = $this->objectManager->get(\Magento\Framework\App\Config\MutableScopeConfigInterface::class);
        $scopeConfig->setValue(
            self::MEDIA_GALLERY_IMAGE_FOLDERS_CONFIG_PATH,
            $this->origConfigValue
        );
    }

    /**
     * Execute method with correct directory path and file name to check that files under WYSIWYG media directory
     * can be removed.
     *
     * @param string $filename
     * @return void
     * @dataProvider executeDataProvider
     */
    public function testExecute(string $filename)
    {
        $filePath =  $this->fullDirectoryPath . DIRECTORY_SEPARATOR . $filename;
        $fixtureDir = realpath(__DIR__ . '/../../../../../Catalog/_files');
        $this->copyFile($fixtureDir . '/' . $this->fileName, $filePath);

        $this->model->getRequest()->setMethod('POST')
            ->setPostValue('files', [$this->imagesHelper->idEncode($filename)]);
        $this->model->getStorage()->getSession()->setCurrentPath($this->fullDirectoryPath);
        $this->model->execute();

        $this->assertFalse(
            $this->mediaDirectory->isExist(
                $this->mediaDirectory->getRelativePath($this->fullDirectoryPath . '/' . $filename)
            )
        );
    }

    /**
     * DataProvider for testExecute
     *
     * @return array
     */
    public static function executeDataProvider(): array
    {
        return [
            ['name with spaces.jpg'],
            ['name with, comma.jpg'],
            ['name with* asterisk.jpg'],
            ['name with[ bracket.jpg'],
            ['magento_small_image.jpg'],
            ['_.jpg'],
        ];
    }

    /**
     * Check that htaccess file couldn't be removed via
     * \Magento\Cms\Controller\Adminhtml\Wysiwyg\Images\DeleteFiles::execute method
     *
     * @return void
     */
    public function testDeleteHtaccess()
    {
        $testDir = $this->imagesHelper->getStorageRoot() . "directory1";
        $this->mediaDirectory->create($this->mediaDirectory->getRelativePath($testDir));
        $path = $testDir . '/.htaccess';
        $denyListPathValidator = $this->objectManager
            ->create(DenyListPathValidator::class, ['driver' => $this->mediaDirectory->getDriver()]);
        $denyListPathValidator->addException($path);
        $bypassDenyListWriteFactory = $this->objectManager->create(WriteFactory::class, [
            'denyListPathValidator' => $denyListPathValidator
        ]);
        $this->bypassDenyListWrite = $bypassDenyListWriteFactory->create(
            $this->mediaDirectory->getAbsolutePath(),
            $this->mediaDirectory->getDriver() instanceof File ? DriverPool::FILE : DriverPool::REMOTE,
            null,
            DirectoryList::MEDIA
        );

        if (!$this->bypassDenyListWrite->isFile($path)) {
            $this->bypassDenyListWrite->writeFile($path, "Order deny,allow\nDeny from all");
        }
        $this->model->getRequest()->setMethod('POST')
            ->setPostValue('files', [$this->imagesHelper->idEncode('.htaccess')]);
        $this->model->getStorage()->getSession()->setCurrentPath($testDir);
        $this->model->execute();

        $this->assertTrue(
            $this->bypassDenyListWrite->isExist(
                $this->bypassDenyListWrite->getRelativePath($testDir . '/' . '.htaccess')
            )
        );
        $this->bypassDenyListWrite->delete($testDir);
    }

    /**
     * Execute method with traversal file path to check that there is no ability to remove file which is not
     * under media directory.
     *
     * @return void
     */
    public function testExecuteWithWrongFileName()
    {
        $fixtureDir = realpath(__DIR__ . '/../../../../../Catalog/_files');
        $this->mediaDirectory->create('secondDir');
        $driver = $this->mediaDirectory->getDriver();
        $driver->filePutContents(
            $this->mediaDirectory->getAbsolutePath('secondDir' . DIRECTORY_SEPARATOR . $this->fileName),
            file_get_contents($fixtureDir . '/' . $this->fileName)
        );
        $fileName = '/../secondDir/' . $this->fileName;
        $this->model->getRequest()->setMethod('POST')
            ->setPostValue('files', [$this->imagesHelper->idEncode($fileName)]);
        $this->model->getStorage()->getSession()->setCurrentPath($this->fullDirectoryPath);
        $this->model->execute();

        $this->assertTrue($this->mediaDirectory->isExist($this->fullDirectoryPath . $fileName));
    }

    /**
     * Execute method with correct directory path and file name to check that files under linked media directory
     * can be removed.
     *
     * @return void
     * @magentoDataFixture Magento/Cms/_files/linked_media.php
     */
    public function testExecuteWithLinkedMedia()
    {
        if (!$this->mediaDirectory->getDriver() instanceof File) {
            self::markTestSkipped('Remote storages like AWS S3 doesn\'t support symlinks');
        }

        $directoryName = 'linked_media';
        $fullDirectoryPath = $this->filesystem->getDirectoryRead(DirectoryList::PUB)
                ->getAbsolutePath() . DIRECTORY_SEPARATOR . $directoryName;
        $filePath =  $fullDirectoryPath . DIRECTORY_SEPARATOR . $this->fileName;
        $fixtureDir = realpath(__DIR__ . '/../../../../../Catalog/_files');
        $this->copyFile($fixtureDir . '/' . $this->fileName, $filePath);

        $wysiwygDir = $this->mediaDirectory->getAbsolutePath() . '/wysiwyg';
        $this->model->getRequest()->setMethod('POST')
            ->setPostValue('files', [$this->imagesHelper->idEncode($this->fileName)]);
        $this->model->getStorage()->getSession()->setCurrentPath($wysiwygDir);
        $this->model->execute();
        $this->assertFalse(is_file($fullDirectoryPath . DIRECTORY_SEPARATOR . $this->fileName));
    }

    /**
     * @inheritdoc
     */
    public static function tearDownAfterClass(): void
    {
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\Framework\Filesystem::class);
        /** @var WriteInterface $directory */
        $directory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        if ($directory->isExist('wysiwyg')) {
            $directory->delete('wysiwyg');
        }
    }

    /**
     * @param string $source
     * @param string $destination
     * @throws FileSystemException
     */
    private function copyFile(string $source, string $destination)
    {
        $driver = $this->mediaDirectory->getDriver();
        $driver->createDirectory(dirname($destination));
        $driver->filePutContents($destination, file_get_contents($source));
    }
}
