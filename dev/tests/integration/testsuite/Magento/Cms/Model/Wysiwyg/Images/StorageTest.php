<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
namespace Magento\Cms\Model\Wysiwyg\Images;

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
            mkdir(self::$_baseDir, 0777);
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
            $this->assertInstanceOf('Magento\Framework\Object', $item);
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
        $filesystem = $objectManager->get('Magento\Framework\App\Filesystem');
        $session = $objectManager->get('Magento\Backend\Model\Session');
        $backendUrl = $objectManager->get('Magento\Backend\Model\UrlInterface');
        $imageFactory = $objectManager->get('Magento\Framework\Image\AdapterFactory');
        $assetRepo = $objectManager->get('Magento\Framework\View\Asset\Repository');
        $imageHelper = $objectManager->get('Magento\Cms\Helper\Wysiwyg\Images');
        $coreFileStorageDb = $objectManager->get('Magento\Core\Helper\File\Storage\Database');
        $storageCollectionFactory = $objectManager->get('Magento\Cms\Model\Wysiwyg\Images\Storage\CollectionFactory');
        $storageFileFactory = $objectManager->get('Magento\Core\Model\File\Storage\FileFactory');
        $storageDatabaseFactory = $objectManager->get('Magento\Core\Model\File\Storage\DatabaseFactory');
        $directoryDatabaseFactory = $objectManager->get('Magento\Core\Model\File\Storage\Directory\DatabaseFactory');
        $uploaderFactory = $objectManager->get('Magento\Core\Model\File\UploaderFactory');

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
            str_replace('\\', '/', $filesystem->getPath(\Magento\Framework\App\Filesystem::MEDIA_DIR)),
            $model->getThumbsPath()
        );
    }
}
