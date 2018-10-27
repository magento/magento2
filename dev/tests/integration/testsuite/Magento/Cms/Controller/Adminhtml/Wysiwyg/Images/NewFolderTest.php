<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Controller\Adminhtml\Wysiwyg\Images;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Test for \Magento\Cms\Controller\Adminhtml\Wysiwyg\Images\NewFolder class.
 */
class NewFolderTest extends \PHPUnit\Framework\TestCase
{
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
<<<<<<< HEAD
    private $fullDirectoryPath;

    /**
     * @var string
     */
=======
>>>>>>> upstream/2.2-develop
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
    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->filesystem = $objectManager->get(\Magento\Framework\Filesystem::class);
        /** @var \Magento\Cms\Helper\Wysiwyg\Images $imagesHelper */
        $this->imagesHelper = $objectManager->get(\Magento\Cms\Helper\Wysiwyg\Images::class);
        $this->mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
<<<<<<< HEAD
        $this->fullDirectoryPath = $this->imagesHelper->getStorageRoot();
=======
>>>>>>> upstream/2.2-develop
        $this->model = $objectManager->get(\Magento\Cms\Controller\Adminhtml\Wysiwyg\Images\NewFolder::class);
    }

    /**
<<<<<<< HEAD
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
=======
     * Execute method with correct directory path to check that new folder can be created under linked media directory.
     */
    public function testExecute()
    {
        $fullDirectoryPath = $this->imagesHelper->getStorageRoot();
        $this->model->getRequest()->setMethod('POST')
            ->setPostValue('name', $this->dirName);
        $this->model->getStorage()->getSession()->setCurrentPath($fullDirectoryPath);
        $this->model->execute();
        $this->assertTrue(
            $this->mediaDirectory->isExist(
                $this->mediaDirectory->getRelativePath(
                    $fullDirectoryPath . DIRECTORY_SEPARATOR . $this->dirName
>>>>>>> upstream/2.2-develop
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
        $linkedDirectoryPath =  $this->filesystem->getDirectoryRead(DirectoryList::PUB)
<<<<<<< HEAD
                ->getAbsolutePath()  . 'linked_media';
        $this->model->getRequest()->setMethod('POST')
            ->setPostValue('name', $this->dirName);
        $this->model->getStorage()
            ->getSession()
            ->setCurrentPath($this->fullDirectoryPath . DIRECTORY_SEPARATOR . 'wysiwyg');
        $this->model->execute();

=======
                ->getAbsolutePath() . DIRECTORY_SEPARATOR . 'linked_media';
        $fullDirectoryPath = $this->imagesHelper->getStorageRoot();
        $this->model->getRequest()->setMethod('POST')
            ->setPostValue('name', $this->dirName);
        $this->model->getStorage()->getSession()->setCurrentPath($fullDirectoryPath);
        $this->model->execute();
>>>>>>> upstream/2.2-develop
        $this->assertTrue(is_dir($linkedDirectoryPath . DIRECTORY_SEPARATOR . $this->dirName));
    }

    /**
<<<<<<< HEAD
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

        $this->assertFileNotExists(
            $this->fullDirectoryPath . $dirPath . $this->dirName
        );
    }

    /**
=======
>>>>>>> upstream/2.2-develop
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
