<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Controller\Adminhtml\Wysiwyg\Images;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\HttpFactory as ResponseFactory;
use Magento\Framework\Filesystem\Driver\File;

/**
 * Test for \Magento\Cms\Controller\Adminhtml\Wysiwyg\Images\DeleteFolder class.
 *
 * @magentoAppArea adminhtml
 */
class DeleteFolderTest extends \PHPUnit\Framework\TestCase
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
     * @var \Magento\Cms\Controller\Adminhtml\Wysiwyg\Images\DeleteFolder
     */
    private $model;

    /**
     * @var  \Magento\Cms\Helper\Wysiwyg\Images
     */
    private $imagesHelper;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $mediaDirectory;

    /**
     * @var string
     */
    private $fullDirectoryPath;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var HttpFactory
     */
    private $responseFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->filesystem = $this->objectManager->get(\Magento\Framework\Filesystem::class);
        $this->mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        /** @var \Magento\Cms\Helper\Wysiwyg\Images $imagesHelper */
        $this->imagesHelper = $this->objectManager->get(\Magento\Cms\Helper\Wysiwyg\Images::class);
        $this->fullDirectoryPath = $this->imagesHelper->getStorageRoot();
        $this->mediaDirectory->create($this->mediaDirectory->getRelativePath($this->fullDirectoryPath));
        $this->responseFactory = $this->objectManager->get(ResponseFactory::class);
        $this->model = $this->objectManager->get(\Magento\Cms\Controller\Adminhtml\Wysiwyg\Images\DeleteFolder::class);
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
        $directoryName = 'testDir';
        $this->mediaDirectory->delete(
            $this->mediaDirectory->getRelativePath($this->imagesHelper->getStorageRoot() . '/' . $directoryName)
        );
        $this->mediaDirectory->delete('secondDir');
        $scopeConfig = $this->objectManager->get(\Magento\Framework\App\Config\MutableScopeConfigInterface::class);
        $scopeConfig->setValue(
            self::MEDIA_GALLERY_IMAGE_FOLDERS_CONFIG_PATH,
            $this->origConfigValue
        );
    }

    /**
     * Execute method with correct directory path to check that directories under WYSIWYG media directory
     * can be removed.
     *
     * @return void
     * @magentoAppIsolation enabled
     */
    public function testExecute()
    {
        $directoryName = 'testDir/NewDirectory';
        $path = $this->mediaDirectory->getRelativePath($this->fullDirectoryPath . $directoryName);
        $this->mediaDirectory->create($path);
        $this->model->getRequest()->setParams(['node' => $this->imagesHelper->idEncode($path)]);
        $this->model->getRequest()->setMethod('POST');
        $this->model->execute();

        $this->assertFalse($this->mediaDirectory->isExist($path));
    }

    /**
     * Execute method with correct directory path to check that directories under linked media directory
     * can be removed.
     *
     * @magentoDataFixture Magento/Cms/_files/linked_media.php
     * @magentoAppIsolation enabled
     */
    public function testExecuteWithLinkedMedia()
    {
        if (!$this->mediaDirectory->getDriver() instanceof File) {
            self::markTestSkipped('Remote storages like AWS S3 doesn\'t support symlinks');
        }

        $linkedDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::PUB);
        $linkedDirectoryPath =  $this->filesystem->getDirectoryRead(DirectoryList::PUB)
                ->getAbsolutePath() . 'linked_media';
        $directoryName = 'NewDirectory';

        $linkedDirectory->create(
            $linkedDirectory->getRelativePath($linkedDirectoryPath . DIRECTORY_SEPARATOR . $directoryName)
        );
        $this->model->getRequest()->setParams(
            ['node' => $this->imagesHelper->idEncode('wysiwyg' . DIRECTORY_SEPARATOR . $directoryName)]
        );
        $this->model->execute();
        $this->assertFalse(is_dir($linkedDirectoryPath . DIRECTORY_SEPARATOR . $directoryName));
    }

    /**
     * Execute method with traversal directory path to check that there is no ability to remove folder which is not
     * under media directory.
     *
     * @return void
     * @magentoAppIsolation enabled
     */
    public function testExecuteWithWrongDirectoryName()
    {
        $secondDir = $this->mediaDirectory->getRelativePath($this->fullDirectoryPath . 'secondDir');
        $this->mediaDirectory->create($secondDir);
        $testDir = $this->mediaDirectory->getRelativePath($this->fullDirectoryPath . 'testDir');
        $this->mediaDirectory->create($testDir);
        $directoryName = 'testDir/../secondDir/';
        $this->assertTrue($this->mediaDirectory->isExist($this->fullDirectoryPath . $directoryName));
        $this->model->getRequest()->setParams(['node' => $this->imagesHelper->idEncode($directoryName)]);
        $this->model->execute();

        $this->assertTrue($this->mediaDirectory->isExist($this->fullDirectoryPath . $directoryName));
    }

    /**
     * Execute method to check that there is no ability to remove folder which is in excluded directories list.
     *
     * @return void
     * @magentoAppIsolation enabled
     */
    public function testExecuteWithExcludedDirectoryName()
    {
        $directoryName = 'downloadable';
        $expectedResponseMessage = 'We cannot delete the selected directory.';
        $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $mediaDirectory->create($directoryName);
        $this->assertTrue($this->mediaDirectory->isExist($this->fullDirectoryPath . $directoryName));

        $this->model->getRequest()->setParams(['node' => $this->imagesHelper->idEncode($directoryName)]);
        $this->model->getRequest()->setMethod('POST');
        $jsonResponse = $this->model->execute();
        $jsonResponse->renderResult($response = $this->responseFactory->create());
        $data = json_decode($response->getBody(), true);

        $this->assertTrue($data['error']);
        $this->assertEquals($expectedResponseMessage, $data['message']);
        $this->assertTrue($this->mediaDirectory->isExist($this->fullDirectoryPath . $directoryName));
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
        if ($directory->isExist('downloadable')) {
            $directory->delete('downloadable');
        }
    }
}
