<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Controller\Adminhtml\Wysiwyg\Images;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;

/**
 * Test for \Magento\Cms\Controller\Adminhtml\Wysiwyg\Images\NewFolder class.
 *
 * @magentoAppArea adminhtml
 */
class NewFolderTest extends \PHPUnit\Framework\TestCase
{
    private const MEDIA_GALLERY_IMAGE_FOLDERS_CONFIG_PATH
        = 'system/media_storage_configuration/allowed_resources/media_gallery_image_folders';

    /**
     * @var array
     */
    private $origConfigValue;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Cms\Controller\Adminhtml\Wysiwyg\Images\NewFolder
     */
    private $model;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $mediaDirectory;

    /**
     * @var string
     */
    private $fullDirectoryPath;

    /**
     * @var string
     */
    private $dirName= 'NewDirectory';

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var \Magento\Cms\Helper\Wysiwyg\Images
     */
    private $imagesHelper;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->filesystem = $this->objectManager->get(\Magento\Framework\Filesystem::class);
        /** @var \Magento\Cms\Helper\Wysiwyg\Images $imagesHelper */
        $this->imagesHelper = $this->objectManager->get(\Magento\Cms\Helper\Wysiwyg\Images::class);
        $this->mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->fullDirectoryPath = $this->imagesHelper->getStorageRoot() . '/testDir';
        $this->mediaDirectory->create($this->mediaDirectory->getRelativePath($this->fullDirectoryPath));
        $this->model = $this->objectManager->get(\Magento\Cms\Controller\Adminhtml\Wysiwyg\Images\NewFolder::class);
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
        $scopeConfig = $this->objectManager->get(\Magento\Framework\App\Config\MutableScopeConfigInterface::class);
        $scopeConfig->setValue(
            self::MEDIA_GALLERY_IMAGE_FOLDERS_CONFIG_PATH,
            $this->origConfigValue
        );
    }

    /**
     * Execute method with correct directory path to check that new folder can be created under WYSIWYG media directory.
     *
     * @return void
     */
    public function testExecute()
    {
        $this->model->getRequest()->setMethod('POST')
            ->setPostValue('name', $this->dirName);
        $this->model->getStorage()->getSession()->setCurrentPath($this->fullDirectoryPath);
        $this->model->execute();

        $this->assertTrue(
            $this->mediaDirectory->isExist(
                $this->mediaDirectory->getRelativePath(
                    $this->fullDirectoryPath . DIRECTORY_SEPARATOR . $this->dirName
                )
            )
        );
    }

    /**
     * Execute method with correct directory path to check that new folder can be created under WYSIWYG media directory.
     *
     * @magentoDataFixture Magento/Cms/_files/linked_media.php
     */
    public function testExecuteWithLinkedMedia()
    {
        if (!$this->mediaDirectory->getDriver() instanceof File) {
            self::markTestSkipped('Remote storages like AWS S3 doesn\'t support symlinks');
        }

        $linkedDirectoryPath =  $this->filesystem->getDirectoryRead(DirectoryList::PUB)
                ->getAbsolutePath() . 'linked_media';
        $this->model->getRequest()->setMethod('POST')
            ->setPostValue('name', $this->dirName);
        $this->model->getStorage()
            ->getSession()
            ->setCurrentPath($this->imagesHelper->getStorageRoot() . DIRECTORY_SEPARATOR . 'wysiwyg');
        $this->model->execute();

        $this->assertTrue(is_dir($linkedDirectoryPath . DIRECTORY_SEPARATOR . $this->dirName));
    }

    /**
     * Execute method with traversal directory path to check that there is no ability to create new folder not
     * under media directory.
     *
     * @return void
     */
    public function testExecuteWithWrongPath()
    {
        $dirPath = '/../../../';
        $this->model->getRequest()->setMethod('POST')
            ->setPostValue('name', $this->dirName);
        $this->model->getStorage()->getSession()->setCurrentPath($this->fullDirectoryPath . $dirPath);
        $this->model->execute();

        $this->assertFileDoesNotExist(
            $this->fullDirectoryPath . $dirPath . $this->dirName
        );
    }

    /**
     * @inheritdoc
     */
    public static function tearDownAfterClass(): void
    {
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\Framework\Filesystem::class);
        /** @var \Magento\Framework\Filesystem\Directory\WriteInterface $directory */
        $directory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        if ($directory->isExist('wysiwyg')) {
            $directory->delete('wysiwyg');
        }
    }
}
