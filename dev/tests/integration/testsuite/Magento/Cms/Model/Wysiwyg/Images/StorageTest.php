<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 */
namespace Magento\Cms\Model\Wysiwyg\Images;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
            \Magento\Cms\Helper\Wysiwyg\Images::class
        )->getCurrentPath() . 'MagentoCmsModelWysiwygImagesStorageTest';
        if (!file_exists(self::$_baseDir)) {
            mkdir(self::$_baseDir);
        }
        touch(self::$_baseDir . '/1.swf');
    }

    public static function tearDownAfterClass()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\Filesystem\Driver\File::class
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
        $objectManager->get(\Magento\Framework\View\DesignInterface::class)
            ->setDesignTheme('Magento/backend');
        /** @var $model \Magento\Cms\Model\Wysiwyg\Images\Storage */
        $model = $objectManager->create(\Magento\Cms\Model\Wysiwyg\Images\Storage::class);
        $collection = $model->getFilesCollection(self::$_baseDir, 'media');
        $this->assertInstanceOf(\Magento\Cms\Model\Wysiwyg\Images\Storage\Collection::class, $collection);
        foreach ($collection as $item) {
            $this->assertInstanceOf(\Magento\Framework\DataObject::class, $item);
            $this->assertStringEndsWith('/1.swf', $item->getUrl());
            $this->assertStringMatchesFormat(
                'http://%s/static/%s/adminhtml/%s/%s/Magento_Cms/images/placeholder_thumbnail.jpg',
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
        $filesystem = $objectManager->get(\Magento\Framework\Filesystem::class);
        $session = $objectManager->get(\Magento\Backend\Model\Session::class);
        $backendUrl = $objectManager->get(\Magento\Backend\Model\UrlInterface::class);
        $imageFactory = $objectManager->get(\Magento\Framework\Image\AdapterFactory::class);
        $assetRepo = $objectManager->get(\Magento\Framework\View\Asset\Repository::class);
        $imageHelper = $objectManager->get(\Magento\Cms\Helper\Wysiwyg\Images::class);
        $coreFileStorageDb = $objectManager->get(\Magento\MediaStorage\Helper\File\Storage\Database::class);
        $storageCollectionFactory = $objectManager->get(
            \Magento\Cms\Model\Wysiwyg\Images\Storage\CollectionFactory::class
        );
        $storageFileFactory = $objectManager->get(\Magento\MediaStorage\Model\File\Storage\FileFactory::class);
        $storageDatabaseFactory = $objectManager->get(\Magento\MediaStorage\Model\File\Storage\DatabaseFactory::class);
        $directoryDatabaseFactory = $objectManager->get(
            \Magento\MediaStorage\Model\File\Storage\Directory\DatabaseFactory::class
        );
        $uploaderFactory = $objectManager->get(\Magento\MediaStorage\Model\File\UploaderFactory::class);

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
