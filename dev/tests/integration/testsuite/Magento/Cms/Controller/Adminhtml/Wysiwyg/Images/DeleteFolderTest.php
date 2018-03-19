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
     * @var string
     */
    private $fullDirectoryPath;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $filesystem = $objectManager->get(\Magento\Framework\Filesystem::class);
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        /** @var \Magento\Cms\Helper\Wysiwyg\Images $imagesHelper */
        $this->imagesHelper = $objectManager->get(\Magento\Cms\Helper\Wysiwyg\Images::class);
        $this->fullDirectoryPath = $this->imagesHelper->getStorageRoot();
        $this->model = $objectManager->get(\Magento\Cms\Controller\Adminhtml\Wysiwyg\Images\DeleteFolder::class);
    }

    /**
     * Execute method with correct directory path to check that directories under WYSIWYG media directory
     * can be removed.
     *
     * @return void
     */
    public function testExecute()
    {
        $directoryName = DIRECTORY_SEPARATOR . 'NewDirectory';
        $this->mediaDirectory->create(
            $this->mediaDirectory->getRelativePath($this->fullDirectoryPath . $directoryName)
        );
        $this->model->getRequest()->setParams(['node' => $this->imagesHelper->idEncode($directoryName)]);
        $this->model->execute();
        $this->assertFalse(
            $this->mediaDirectory->isExist(
                $this->mediaDirectory->getRelativePath(
                    $this->fullDirectoryPath . $directoryName
                )
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
    }
}
