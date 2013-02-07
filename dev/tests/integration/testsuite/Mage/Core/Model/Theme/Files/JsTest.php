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
 * @package     Mage_Core
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test for js files
 */
class Mage_Core_Model_Theme_Customization_Files_JsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @magentoDbIsolation enabled
     * @dataProvider fileSampleData
     */
    public function testSaveJsFile($data)
    {
        /** @var $themeModel Mage_Core_Model_Theme */
        $themeModel = Mage::getObjectManager()->create('Mage_Core_Model_Theme');
        $theme = $themeModel->getCollection()->getFirstItem();

        /** @var $jsFileModel Mage_Core_Model_Theme_Customization_Files_Js */
        $jsFileModel = Mage::getObjectManager()->create('Mage_Core_Model_Theme_Customization_Files_Js');
        $file = $jsFileModel->saveJsFile($theme, $data);

        $this->assertNotEmpty($file->getId());
    }

    /**
     * @magentoDbIsolation enabled
     * @dataProvider fileSampleData
     */
    public function testGetCollectionByTheme($data)
    {
        /** @var $themeModel Mage_Core_Model_Theme */
        $themeModel = Mage::getObjectManager()->create('Mage_Core_Model_Theme');
        $theme = $themeModel->getCollection()->getFirstItem();

        /** @var $jsFileModel Mage_Core_Model_Theme_Customization_Files_Js */
        $jsFileModel = Mage::getObjectManager()->create('Mage_Core_Model_Theme_Customization_Files_Js');
        $oldJsFilesCount = $jsFileModel->getCollectionByTheme($theme)->count();
        $oldJsFilesCount++;
        $jsFileModel->saveJsFile($theme, $data);

        $this->assertEquals($oldJsFilesCount, $jsFileModel->getCollectionByTheme($theme)->count());
    }

    /**
     * @magentoDbIsolation enabled
     * @dataProvider fileSampleData
     */
    public function testSaveData($data)
    {
        /** @var $themeModel Mage_Core_Model_Theme */
        $themeModel = Mage::getObjectManager()->create('Mage_Core_Model_Theme');
        $theme = $themeModel->getCollection()->getFirstItem();

        /** @var $jsFileModel Mage_Core_Model_Theme_Customization_Files_Js */
        $jsFileModel = Mage::getObjectManager()->create('Mage_Core_Model_Theme_Customization_Files_Js');
        /** @var $file  */
        $file = $jsFileModel->saveJsFile($theme, $data);

        $jsFileModel->setDataForSave($file->getId());
        $jsFileModel->saveData($theme);

        /** @var $updatedFile Mage_Core_Model_Theme_Files */
        $updatedFile = Mage::getObjectManager()->create('Mage_Core_Model_Theme_Files');
        $updatedFile->load($file->getId());

        $this->assertFalse((bool)$updatedFile->getIsTemporary());
    }

    /**
     * @magentoDbIsolation enabled
     * @dataProvider fileSampleData
     */
    public function testRemoveTemporaryFiles($data)
    {
        /** @var $themeModel Mage_Core_Model_Theme */
        $themeModel = Mage::getObjectManager()->create('Mage_Core_Model_Theme');
        $theme = $themeModel->getCollection()->getFirstItem();

        /** @var $jsFileModel Mage_Core_Model_Theme_Customization_Files_Js */
        $jsFileModel = Mage::getObjectManager()->create('Mage_Core_Model_Theme_Customization_Files_Js');
        $jsFileModel->saveJsFile($theme, $data);

        $oldJsFilesCount = $jsFileModel->getCollectionByTheme($theme)->count();
        $jsFiles = $jsFileModel->getCollectionByTheme($theme);

        $temporaryFilesCount = 0;
        foreach ($jsFiles as $file) {
            if ($file->getIsTemporary()) {
                $temporaryFilesCount++;
            }
        }

        $jsFileModel->removeTemporaryFiles($theme);

        $expectedFilesCount = $oldJsFilesCount - $temporaryFilesCount;
        $this->assertEquals($expectedFilesCount, $jsFileModel->getCollectionByTheme($theme)->count());
    }

    /**
     * File sample data
     *
     * @return array
     */
    public function fileSampleData()
    {
        return array(array(array(
            'name'    => 'js_test_file.js',
            'content' => 'js file content',
        )));
    }
}
