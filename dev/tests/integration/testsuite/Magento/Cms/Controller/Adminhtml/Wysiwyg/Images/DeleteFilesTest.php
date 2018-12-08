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
<<<<<<< HEAD
=======
    private $fullDirectoryPath;

    /**
     * @var string
     */
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
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
<<<<<<< HEAD
     * @var string
     */
    private $fullDirectoryPath;

    /**
=======
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
<<<<<<< HEAD
=======
        $directoryName = 'directory1';
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
        $this->filesystem = $this->objectManager->get(\Magento\Framework\Filesystem::class);
        /** @var \Magento\Cms\Helper\Wysiwyg\Images $imagesHelper */
        $this->imagesHelper = $this->objectManager->get(\Magento\Cms\Helper\Wysiwyg\Images::class);
        $this->mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
<<<<<<< HEAD
        $this->model = $this->objectManager->get(\Magento\Cms\Controller\Adminhtml\Wysiwyg\Images\DeleteFiles::class);
        $this->fullDirectoryPath = $this->imagesHelper->getStorageRoot() . '/directory1';
=======
        $this->fullDirectoryPath = $this->imagesHelper->getStorageRoot() . '/' . $directoryName;
        $this->mediaDirectory->create($this->mediaDirectory->getRelativePath($this->fullDirectoryPath));
        $filePath =  $this->fullDirectoryPath . DIRECTORY_SEPARATOR . $this->fileName;
        $fixtureDir = realpath(__DIR__ . '/../../../../../Catalog/_files');
        copy($fixtureDir . '/' . $this->fileName, $filePath);
        $path = $this->fullDirectoryPath . '/.htaccess';
        if (!$this->mediaDirectory->isFile($path)) {
            $this->mediaDirectory->writeFile($path, "Order deny,allow\nDeny from all");
        }
        $this->model = $this->objectManager->get(\Magento\Cms\Controller\Adminhtml\Wysiwyg\Images\DeleteFiles::class);
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
    }

    /**
     * Execute method with correct directory path and file name to check that files under WYSIWYG media directory
     * can be removed.
     *
     * @return void
     */
    public function testExecute()
    {
<<<<<<< HEAD
        $this->mediaDirectory->create($this->mediaDirectory->getRelativePath($this->fullDirectoryPath));
        $filePath =  $this->fullDirectoryPath . DIRECTORY_SEPARATOR . $this->fileName;
        $fixtureDir = realpath(__DIR__ . '/../../../../../Catalog/_files');
        copy($fixtureDir . '/' . $this->fileName, $filePath);

        $this->executeFileDelete($this->fullDirectoryPath, $this->fileName);
=======
        $this->model->getRequest()->setMethod('POST')
            ->setPostValue('files', [$this->imagesHelper->idEncode($this->fileName)]);
        $this->model->getStorage()->getSession()->setCurrentPath($this->fullDirectoryPath);
        $this->model->execute();

>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
        $this->assertFalse(
            $this->mediaDirectory->isExist(
                $this->mediaDirectory->getRelativePath($this->fullDirectoryPath . '/' . $this->fileName)
            )
        );
    }

    /**
<<<<<<< HEAD
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
        $this->executeFileDelete($wysiwygDir, $this->fileName);
        $this->assertFalse(is_file($fullDirectoryPath . DIRECTORY_SEPARATOR . $this->fileName));
    }

    /**
=======
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     * Check that htaccess file couldn't be removed via
     * \Magento\Cms\Controller\Adminhtml\Wysiwyg\Images\DeleteFiles::execute method
     *
     * @return void
     */
    public function testDeleteHtaccess()
    {
<<<<<<< HEAD
        $this->createFile($this->fullDirectoryPath, '.htaccess');
        $this->executeFileDelete($this->fullDirectoryPath, '.htaccess');

        $this->assertTrue(
            $this->mediaDirectory->isExist(
                $this->mediaDirectory->getRelativePath($this->fullDirectoryPath . '/.htaccess')
=======
        $this->model->getRequest()->setMethod('POST')
            ->setPostValue('files', [$this->imagesHelper->idEncode('.htaccess')]);
        $this->model->getStorage()->getSession()->setCurrentPath($this->fullDirectoryPath);
        $this->model->execute();

        $this->assertTrue(
            $this->mediaDirectory->isExist(
                $this->mediaDirectory->getRelativePath($this->fullDirectoryPath . '/' . '.htaccess')
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
            )
        );
    }

    /**
<<<<<<< HEAD
     * Check that random file could be removed via
     * \Magento\Cms\Controller\Adminhtml\Wysiwyg\Images\DeleteFiles::execute method
     *
     * @return void
     */
    public function testDeleteAnyFile()
    {
        $this->createFile($this->fullDirectoryPath, 'ahtaccess');
        $this->executeFileDelete($this->fullDirectoryPath, 'ahtaccess');

        $this->assertFalse(
            $this->mediaDirectory->isExist(
                $this->mediaDirectory->getRelativePath($this->fullDirectoryPath . '/ahtaccess')
            )
        );
    }

    /**
     * Create file.
     *
     * @param string $path
     * @param string $fileName
     * @return void
     */
    private function createFile(string $path, string $fileName)
    {
        $file = $path . '/' . $fileName;
        if (!$this->mediaDirectory->isFile($file)) {
            $this->mediaDirectory->writeFile($file, 'Content');
        }
    }

    /**
     * Execute file delete operation.
     *
     * @param string $path
     * @param string $fileName
     * @return void
     */
    private function executeFileDelete(string $path, string $fileName)
    {
        $this->model->getRequest()->setMethod('POST')
            ->setPostValue('files', [$this->imagesHelper->idEncode($fileName)]);
        $this->model->getStorage()->getSession()->setCurrentPath($path);
        $this->model->execute();
=======
     * Execute method with traversal file path to check that there is no ability to remove file which is not
     * under media directory.
     *
     * @return void
     */
    public function testExecuteWithWrongFileName()
    {
        $fileName = '/../../../etc/env.php';
        $this->model->getRequest()->setMethod('POST')
            ->setPostValue('files', [$this->imagesHelper->idEncode($fileName)]);
        $this->model->getStorage()->getSession()->setCurrentPath($this->fullDirectoryPath);
        $this->model->execute();

        $this->assertFileExists($this->fullDirectoryPath . $fileName);
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
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
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
