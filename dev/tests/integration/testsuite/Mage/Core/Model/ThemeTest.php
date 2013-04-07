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

class Mage_Core_Model_ThemeTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test crud operations for theme model using valid data
     *
     * @magentoDbIsolation enabled
     */
    public function testCrud()
    {
        Mage::getConfig();
        /** @var $themeModel Mage_Core_Model_Theme */
        $themeModel = Mage::getObjectManager()->create('Mage_Core_Model_Theme');
        $themeModel->setData($this->_getThemeValidData());

        $crud = new Magento_Test_Entity($themeModel, array('theme_version' => '2.0.0.1'));
        $crud->testCrud();
    }

    /**
     * Load from configuration
     */
    public function testLoadFromConfiguration()
    {
        $designPath = __DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'design';
        $themePath = implode(DS, array('frontend', 'default', 'default', 'theme.xml'));

        /** @var $themeModel Mage_Core_Model_Theme */
        $themeModel = Mage::getObjectManager()->create('Mage_Core_Model_Theme');
        $theme = $themeModel->getCollectionFromFilesystem()
            ->setBaseDir($designPath)
            ->addTargetPattern($themePath)
            ->getFirstItem();

        $this->assertEquals($this->_expectedThemeDataFromConfiguration(), $theme->getData());
    }

    /**
     * Expected theme data from configuration
     *
     * @return array
     */
    public function _expectedThemeDataFromConfiguration()
    {
        return array(
            'area'                 => 'frontend',
            'theme_title'          => 'Default',
            'theme_version'        => '2.0.0.0',
            'parent_id'            => null,
            'parent_theme_path'    => null,
            'is_featured'          => true,
            'magento_version_from' => '2.0.0.0-dev1',
            'magento_version_to'   => '*',
            'theme_path'           => 'default/default',
            'preview_image'        => null,
            'theme_directory'      => implode(
                DIRECTORY_SEPARATOR, array(__DIR__, '_files', 'design', 'frontend', 'default', 'default')
            )
        );
    }

    /**
     * Get theme valid data
     *
     * @return array
     */
    protected function _getThemeValidData()
    {
        return array(
            'area'                 => 'space_area',
            'theme_title'          => 'Space theme',
            'theme_version'        => '2.0.0.0',
            'parent_id'            => null,
            'is_featured'          => false,
            'magento_version_from' => '2.0.0.0-dev1',
            'magento_version_to'   => '*',
            'theme_path'           => 'default/space',
            'preview_image'        => 'images/preview.png',
            'type'                 => Mage_Core_Model_Theme::TYPE_VIRTUAL
        );
    }

    /**
     * Test is theme present in file system
     *
     * @magentoAppIsolation enabled
     * @covers Mage_Core_Model_Theme::isPresentInFilesystem
     */
    public function testIsPresentInFilesystem()
    {
        /** @var $themeModel Mage_Core_Model_Theme */
        $themeModel = Mage::getObjectManager()->create('Mage_Core_Model_Theme');
        $themeModel->setData($this->_getThemeValidData());

        $this->assertTrue(!$themeModel->isPresentInFilesystem());
    }

    public function testGetLabelsCollection()
    {
        /** @var $themeModel Mage_Core_Model_Theme */
        $themeModel = Mage::getModel('Mage_Core_Model_Theme');

        /** @var $expectedCollection Mage_Core_Model_Resource_Theme_Collection */
        $expectedCollection = Mage::getModel('Mage_Core_Model_Resource_Theme_Collection');
        $expectedCollection->addAreaFilter(Mage_Core_Model_App_Area::AREA_FRONTEND)
            ->filterVisibleThemes();

        $expectedItemsCount = count($expectedCollection);

        $labelsCollection = $themeModel->getLabelsCollection();
        $this->assertEquals($expectedItemsCount, count($labelsCollection));

        $labelsCollection = $themeModel->getLabelsCollection('-- Please Select --');
        $this->assertEquals(++$expectedItemsCount, count($labelsCollection));
    }

    /**
     * Test theme on child relations
     */
    public function testChildRelation()
    {
        /** @var $theme Mage_Core_Model_Theme */
        /** @var $currentTheme Mage_Core_Model_Theme */
        $theme = Mage::getObjectManager()->get('Mage_Core_Model_Theme');
        $collection = $theme->getCollection()->addTypeFilter(Mage_Core_Model_Theme::TYPE_VIRTUAL);
        foreach ($collection as $currentTheme) {
            $parentTheme = $currentTheme->getParentTheme();
            if (!empty($parentTheme)) {
                $this->assertTrue($parentTheme->hasChildThemes());
            }
        }
    }

    /**
     * @magentoDbIsolation enabled
     * @dataProvider getJsCustomizationProvider
     * @param array $filesData
     * @param array $expectedData
     */
    public function testJsCustomization($filesData, $expectedData)
    {
        /** @var $theme Mage_Core_Model_Theme */
        /** @var $themeModel Mage_Core_Model_Theme */
        $theme = Mage::getObjectManager()->create('Mage_Core_Model_Theme');
        $themeModel = $theme->getCollection()->getFirstItem();

        foreach ($filesData as $fileData) {
            /** @var $filesModel Mage_Core_Model_Theme_File */
            $filesModel = Mage::getObjectManager()->create('Mage_Core_Model_Theme_File');
            $fileData['theme_id'] = $themeModel->getId();
            $filesModel->setData($fileData)
                ->save();
        }

        /** @var $filesJs Mage_Core_Model_Theme_Customization_Files_Js */
        $filesJs = Mage::getObjectManager()->create('Mage_Core_Model_Theme_Customization_Files_Js');
        $themeFilesCollection = $themeModel->setCustomization($filesJs)
            ->getCustomizationData(Mage_Core_Model_Theme_Customization_Files_Js::TYPE);
        $this->assertInstanceOf('Mage_Core_Model_Resource_Theme_File_Collection', $themeFilesCollection);
        $themeFiles = $themeFilesCollection->toArray();
        foreach ($themeFiles['items'] as &$themeFile) {
            $this->assertEquals($themeModel->getId(), $themeFile['theme_id']);
            unset($themeFile['theme_id']);
            unset($themeFile['theme_files_id']);
        }
        $this->assertEquals($expectedData, $themeFiles['items']);
    }

    /**
     * @return array
     */
    public function getJsCustomizationProvider()
    {
        return array(
            array(
                'filesData' => array(
                    array(
                        'file_path'    => 'test_1.js',
                        'file_type'    => Mage_Core_Model_Theme_File::TYPE_JS,
                        'content'      => 'content 1',
                        'sort_order'   => '1'
                    ),
                    array(
                        'file_path'    => 'test_2.js',
                        'file_type'    => Mage_Core_Model_Theme_File::TYPE_JS,
                        'content'      => 'content 2',
                        'sort_order'   => '3'
                    ),
                    array(
                        'file_path'    => 'test_3.js',
                        'file_type'    => Mage_Core_Model_Theme_File::TYPE_JS,
                        'content'      => 'content 3',
                        'sort_order'   => '2'
                    ),
                    array(
                        'file_path'    => 'test_not_js.js',
                        'file_type'    => Mage_Core_Model_Theme_File::TYPE_CSS,
                        'content'      => 'content css',
                        'sort_order'   => ''
                    )
                ),
                'expectedData' => array(
                    array(
                        'file_path'    => 'test_1.js',
                        'file_type'    => Mage_Core_Model_Theme_File::TYPE_JS,
                        'content'      => 'content 1',
                        'sort_order'   => '1',
                        'is_temporary' => '0'
                    ),
                    array(
                        'file_path'    => 'test_3.js',
                        'file_type'    => Mage_Core_Model_Theme_File::TYPE_JS,
                        'content'      => 'content 3',
                        'sort_order'   => '2',
                        'is_temporary' => '0'
                    ),
                    array(
                        'file_path'    => 'test_2.js',
                        'file_type'    => Mage_Core_Model_Theme_File::TYPE_JS,
                        'content'      => 'content 2',
                        'sort_order'   => '3',
                        'is_temporary' => '0'
        ))));
    }
}
