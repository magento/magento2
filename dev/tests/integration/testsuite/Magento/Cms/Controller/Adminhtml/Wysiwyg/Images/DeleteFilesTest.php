<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Controller\Adminhtml\Wysiwyg\Images;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Tests Magento\Cms\Controller\Adminhtml\Wysiwyg\Images\DeleteFiles.
 */
class DeleteFilesTest extends \PHPUnit_Framework_TestCase
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
    private $fullDirectoryPath;

    /**
     * @var string
     */
    private $fileName = 'magento_small_image.jpg';

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $directoryName = 'directory1';
        $filesystem = $objectManager->get(\Magento\Framework\Filesystem::class);
        /** @var \Magento\Cms\Helper\Wysiwyg\Images $imagesHelper */
        $this->imagesHelper = $objectManager->get(\Magento\Cms\Helper\Wysiwyg\Images::class);
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->fullDirectoryPath = $this->imagesHelper->getStorageRoot() . '/' . $directoryName;
        $this->mediaDirectory->create($this->mediaDirectory->getRelativePath($this->fullDirectoryPath));
        $filePath =  $this->fullDirectoryPath . DIRECTORY_SEPARATOR . $this->fileName;
        $fixtureDir = realpath(__DIR__ . '/../../../../../Catalog/_files');
        copy($fixtureDir . '/' . $this->fileName, $filePath);
        $this->model = $objectManager->get(\Magento\Cms\Controller\Adminhtml\Wysiwyg\Images\DeleteFiles::class);
    }

    /**
     * Execute method with correct directory path and file name to check that files under WYSIWYG media directory
     * can be removed.
     *
     * @return void
     */
    public function testExecute()
    {
        $this->model->getRequest()->setMethod('POST')
            ->setPostValue('files', [$this->imagesHelper->idEncode($this->fileName)]);
        $this->model->getStorage()->getSession()->setCurrentPath($this->fullDirectoryPath);
        $this->model->execute();
        $this->assertFalse(
            $this->mediaDirectory->isExist(
                $this->mediaDirectory->getRelativePath($this->fullDirectoryPath . '/' . $this->fileName)
            )
        );
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
        if ($directory->isExist('.htaccess')) {
            $directory->delete('.htaccess');
        }
        if ($directory->isExist('thtaccess')) {
            $directory->delete('thtaccess');
        }
    }

    /**
     * Creates file and tries to delete it via
     * \Magento\Cms\Controller\Adminhtml\Wysiwyg\Images\DeleteFiles::execute method
     *
     * @param  string $fileName
     * @return void
     */
    private function createFileAndExecuteDelete($fileName)
    {
        $path = '/' . $fileName;
        if (!$this->mediaDirectory->isFile($path)) {
            $this->mediaDirectory->writeFile($path, "Order deny,allow\nDeny from all");
        }
        $this->model->getRequest()->setMethod('POST')
            ->setPostValue('files', [$this->imagesHelper->idEncode($fileName)]);
        $this->model->getStorage()->getSession()->setCurrentPath($this->mediaDirectory->getAbsolutePath());
        $this->model->execute();
    }

    /**
     * Check that htaccess file couldn't be removed via
     * \Magento\Cms\Controller\Adminhtml\Wysiwyg\Images\DeleteFiles::execute method
     *
     * @return void
     */
    public function testCouldNotDeleteHtaccess()
    {
        $fileName = '.htaccess';
        $this->createFileAndExecuteDelete($fileName);
        $this->assertTrue(
            $this->mediaDirectory->isExist(
                $this->mediaDirectory->getRelativePath($fileName)
            )
        );
    }

    /**
     * Check that random file could be removed via
     * \Magento\Cms\Controller\Adminhtml\Wysiwyg\Images\DeleteFiles::execute method
     *
     * @return void
     */
    public function testDeleteAnyFile()
    {
        $fileName = 'thtaccess';
        $this->createFileAndExecuteDelete($fileName);
        $this->assertFalse(
            $this->mediaDirectory->isExist(
                $this->mediaDirectory->getRelativePath($fileName)
            )
        );
    }
}
