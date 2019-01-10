<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Controller\Adminhtml\Wysiwyg\Images;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Test for \Magento\Cms\Controller\Adminhtml\Wysiwyg\Images\DeleteFolder class.
 */
class DeleteFolderTest extends \PHPUnit\Framework\TestCase
{
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
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->filesystem = $objectManager->get(\Magento\Framework\Filesystem::class);
        $this->mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        /** @var \Magento\Cms\Helper\Wysiwyg\Images $imagesHelper */
        $this->imagesHelper = $objectManager->get(\Magento\Cms\Helper\Wysiwyg\Images::class);
        $this->model = $objectManager->get(\Magento\Cms\Controller\Adminhtml\Wysiwyg\Images\DeleteFolder::class);
    }

    /**
     * Execute method with correct directory path to check that directories under WYSIWYG media directory
     * can be removed.
     */
    public function testExecute()
    {
        $fullDirectoryPath = $this->imagesHelper->getStorageRoot();
        $directoryName = DIRECTORY_SEPARATOR . 'NewDirectory';
        $this->mediaDirectory->create(
            $this->mediaDirectory->getRelativePath($fullDirectoryPath . $directoryName)
        );
        $this->model->getRequest()->setParams(['node' => $this->imagesHelper->idEncode($directoryName)]);
        $this->model->getRequest()->setMethod('POST');
        $this->model->execute();
        $this->assertFalse(
            $this->mediaDirectory->isExist(
                $this->mediaDirectory->getRelativePath(
                    $fullDirectoryPath . $directoryName
                )
            )
        );
    }

    /**
     * Execute method with correct directory path to check that directories under linked media directory
     * can be removed.
     *
     * @magentoDataFixture Magento/Cms/_files/linked_media.php
     */
    public function testExecuteWithLinkedMedia()
    {
        $linkedDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::PUB);
        $linkedDirectoryPath =  $this->filesystem->getDirectoryRead(DirectoryList::PUB)
                ->getAbsolutePath() . 'linked_media';
        $directoryName = 'NewDirectory';

        $linkedDirectory->create(
            $linkedDirectory->getRelativePath($linkedDirectoryPath . DIRECTORY_SEPARATOR . $directoryName)
        );
        $this->model->getRequest()->setParams(['node' => $this->imagesHelper->idEncode($directoryName)]);
        $this->model->execute();
        $this->assertFalse(is_dir($linkedDirectoryPath . DIRECTORY_SEPARATOR . $directoryName));
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
