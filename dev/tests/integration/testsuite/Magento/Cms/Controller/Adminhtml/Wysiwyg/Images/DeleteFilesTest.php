<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Controller\Adminhtml\Wysiwyg\Images;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Test for \Magento\Cms\Controller\Adminhtml\Wysiwyg\Images\DeleteFiles class.
 */
class DeleteFilesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Cms\Controller\Adminhtml\Wysiwyg\Images\DeleteFiles
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
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->filesystem = $this->objectManager->get(\Magento\Framework\Filesystem::class);
        /** @var \Magento\Cms\Helper\Wysiwyg\Images $imagesHelper */
        $this->imagesHelper = $this->objectManager->get(\Magento\Cms\Helper\Wysiwyg\Images::class);
        $this->mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->model = $this->objectManager->get(\Magento\Cms\Controller\Adminhtml\Wysiwyg\Images\DeleteFiles::class);
    }

    /**
     * Execute method with correct directory path and file name to check that files under WYSIWYG media directory
     * can be removed.
     *
     * @return void
     */
    public function testExecute()
    {
        $directoryName = 'directory1';
        $fullDirectoryPath = $this->imagesHelper->getStorageRoot() . '/' . $directoryName;
        $this->mediaDirectory->create($this->mediaDirectory->getRelativePath($fullDirectoryPath));
        $filePath =  $fullDirectoryPath . DIRECTORY_SEPARATOR . $this->fileName;
        $fixtureDir = realpath(__DIR__ . '/../../../../../Catalog/_files');
        copy($fixtureDir . '/' . $this->fileName, $filePath);

        $this->model->getRequest()->setMethod('POST')
            ->setPostValue('files', [$this->imagesHelper->idEncode($this->fileName)]);
        $this->model->getStorage()->getSession()->setCurrentPath($fullDirectoryPath);
        $this->model->execute();
        $this->assertFalse(
            $this->mediaDirectory->isExist(
                $this->mediaDirectory->getRelativePath($fullDirectoryPath . '/' . $this->fileName)
            )
        );
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
        $directoryName = 'linked_media';
        $fullDirectoryPath = $this->filesystem->getDirectoryRead(DirectoryList::PUB)
                ->getAbsolutePath() . DIRECTORY_SEPARATOR . $directoryName;
        $filePath =  $fullDirectoryPath . DIRECTORY_SEPARATOR . $this->fileName;
        $fixtureDir = realpath(__DIR__ . '/../../../../../Catalog/_files');
        copy($fixtureDir . '/' . $this->fileName, $filePath);

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
    public static function tearDownAfterClass()
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
