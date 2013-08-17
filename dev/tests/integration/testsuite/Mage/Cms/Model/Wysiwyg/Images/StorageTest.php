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
 * @category    Magento
 * @package     Mage_Cms
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Cms_Model_Wysiwyg_Images_StorageTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var string
     */
    protected static $_baseDir;

    public static function setUpBeforeClass()
    {
        self::$_baseDir = Mage::helper('Mage_Cms_Helper_Wysiwyg_Images')->getCurrentPath() . __CLASS__;
        mkdir(self::$_baseDir, 0777);
        touch(self::$_baseDir . DIRECTORY_SEPARATOR . '1.swf');
    }

    public static function tearDownAfterClass()
    {
        Varien_Io_File::rmdirRecursive(self::$_baseDir);
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetFilesCollection()
    {
        Mage::getDesign()->setDesignTheme('default/basic', 'adminhtml');
        /** @var $model Mage_Cms_Model_Wysiwyg_Images_Storage */
        $model = Mage::getModel('Mage_Cms_Model_Wysiwyg_Images_Storage');
        $collection = $model->getFilesCollection(self::$_baseDir, 'media');
        $this->assertInstanceOf('Mage_Cms_Model_Wysiwyg_Images_Storage_Collection', $collection);
        foreach ($collection as $item) {
            $this->assertInstanceOf('Varien_Object', $item);
            $this->assertStringEndsWith('/1.swf', $item->getUrl());
            $this->assertStringMatchesFormat(
                'http://%s/static/adminhtml/%s/%s/%s/Mage_Cms/images/placeholder_thumbnail.jpg',
                $item->getThumbUrl()
            );
            return;
        }
    }

    public function testGetThumbsPath()
    {
        $filesystem = new Magento_Filesystem(new Magento_Filesystem_Adapter_Local);
        $objectManager = Mage::getObjectManager();
        $imageFactory = $objectManager->get('Mage_Core_Model_Image_AdapterFactory');
        $viewUrl = $objectManager->get('Mage_Core_Model_View_Url');
        $model = new Mage_Cms_Model_Wysiwyg_Images_Storage($filesystem, $imageFactory, $viewUrl);
        $this->assertStringStartsWith(
            realpath(Magento_Test_Helper_Bootstrap::getInstance()->getAppInstallDir()),
            $model->getThumbsPath()
        );
    }
}
