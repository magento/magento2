<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 *
 */
namespace Magento\Cms\Model\Wysiwyg\Images;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class StorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected static $_baseDir;

    public static function setUpBeforeClass()
    {
        self::$_baseDir = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Cms\Helper\Wysiwyg\Images'
        )->getCurrentPath() . 'MagentoCmsModelWysiwygImagesStorageTest';
        if (!file_exists(self::$_baseDir)) {
            mkdir(self::$_baseDir, 0770);
        }
        touch(self::$_baseDir . '/1.swf');
    }

    public static function tearDownAfterClass()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\Filesystem\Driver\File'
        )->deleteDirectory(
            self::$_baseDir
        );
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetFilesCollection()
    {
        \Magento\TestFramework\Helper\Bootstrap::getInstance()
            ->loadArea(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE);
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get('Magento\Framework\View\DesignInterface')
            ->setDesignTheme('Magento/backend');
        /** @var $model \Magento\Cms\Model\Wysiwyg\Images\Storage */
        $model = $objectManager->create('Magento\Cms\Model\Wysiwyg\Images\Storage');
        $collection = $model->getFilesCollection(self::$_baseDir, 'media');
        $this->assertInstanceOf('Magento\Cms\Model\Wysiwyg\Images\Storage\Collection', $collection);
        foreach ($collection as $item) {
            $this->assertInstanceOf('Magento\Framework\DataObject', $item);
            $this->assertStringEndsWith('/1.swf', $item->getUrl());
            $this->assertStringMatchesFormat(
                'http://%s/static/adminhtml/%s/%s/Magento_Cms/images/placeholder_thumbnail.jpg',
                $item->getThumbUrl()
            );
            return;
        }
    }

    /**
     * @magentoAppArea adminhtml
     */
    public function testGetThumbsPath()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Framework\Filesystem $filesystem */
        $filesystem = $objectManager->get('Magento\Framework\Filesystem');
        $session = $objectManager->get('Magento\Backend\Model\Session');
        $backendUrl = $objectManager->get('Magento\Backend\Model\UrlInterface');
        $imageFactory = $objectManager->get('Magento\Framework\Image\AdapterFactory');
        $assetRepo = $objectManager->get('Magento\Framework\View\Asset\Repository');
        $imageHelper = $objectManager->get('Magento\Cms\Helper\Wysiwyg\Images');
        $coreFileStorageDb = $objectManager->get('Magento\MediaStorage\Helper\File\Storage\Database');
        $storageCollectionFactory = $objectManager->get('Magento\Cms\Model\Wysiwyg\Images\Storage\CollectionFactory');
        $storageFileFactory = $objectManager->get('Magento\MediaStorage\Model\File\Storage\FileFactory');
        $storageDatabaseFactory = $objectManager->get('Magento\MediaStorage\Model\File\Storage\DatabaseFactory');
        $directoryDatabaseFactory = $objectManager->get(
            'Magento\MediaStorage\Model\File\Storage\Directory\DatabaseFactory'
        );
        $uploaderFactory = $objectManager->get('Magento\MediaStorage\Model\File\UploaderFactory');

        $model = new \Magento\Cms\Model\Wysiwyg\Images\Storage(
            $session,
            $backendUrl,
            $imageHelper,
            $coreFileStorageDb,
            $filesystem,
            $imageFactory,
            $assetRepo,
            $storageCollectionFactory,
            $storageFileFactory,
            $storageDatabaseFactory,
            $directoryDatabaseFactory,
            $uploaderFactory
        );
        $this->assertStringStartsWith(
            $filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath(),
            $model->getThumbsPath()
        );
    }
}
