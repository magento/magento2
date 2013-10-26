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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
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
        self::$_baseDir = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
                ->get('Magento\Cms\Helper\Wysiwyg\Images')
                ->getCurrentPath() . 'MagentoCmsModelWysiwygImagesStorageTest';
        mkdir(self::$_baseDir, 0777);
        touch(self::$_baseDir . DIRECTORY_SEPARATOR . '1.swf');
    }

    public static function tearDownAfterClass()
    {
        \Magento\Io\File::rmdirRecursive(self::$_baseDir);
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetFilesCollection()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\View\DesignInterface')
            ->setDesignTheme('magento_basic', 'adminhtml');
        /** @var $model \Magento\Cms\Model\Wysiwyg\Images\Storage */
        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Cms\Model\Wysiwyg\Images\Storage');
        $collection = $model->getFilesCollection(self::$_baseDir, 'media');
        $this->assertInstanceOf('Magento\Cms\Model\Wysiwyg\Images\Storage\Collection', $collection);
        foreach ($collection as $item) {
            $this->assertInstanceOf('Magento\Object', $item);
            $this->assertStringEndsWith('/1.swf', $item->getUrl());
            $this->assertStringMatchesFormat(
                'http://%s/static/adminhtml/%s/%s/Magento_Cms/images/placeholder_thumbnail.jpg',
                $item->getThumbUrl()
            );
            return;
        }
    }

    public function testGetThumbsPath()
    {
        $filesystem = new \Magento\Filesystem(new \Magento\Filesystem\Adapter\Local);
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $session = $objectManager->get('Magento\Backend\Model\Session');
        $backendUrl = $objectManager->get('Magento\Backend\Model\Url');
        $imageFactory = $objectManager->get('Magento\Core\Model\Image\AdapterFactory');
        $viewUrl = $objectManager->get('Magento\Core\Model\View\Url');
        $imageHelper = $objectManager->get('Magento\Cms\Helper\Wysiwyg\Images');
        $coreFileStorageDb = $objectManager->get('Magento\Core\Helper\File\Storage\Database');
        $dir = $objectManager->get('Magento\App\Dir');
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
            $viewUrl,
            $dir,
            $storageCollectionFactory,
            $storageFileFactory,
            $storageDatabaseFactory,
            $directoryDatabaseFactory,
            $uploaderFactory
        );
        $this->assertStringStartsWith(
            realpath(\Magento\TestFramework\Helper\Bootstrap::getInstance()->getAppInstallDir()),
            $model->getThumbsPath()
        );
    }
}
