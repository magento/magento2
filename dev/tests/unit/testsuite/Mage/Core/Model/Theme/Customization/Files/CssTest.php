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
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test theme js file model
 */
class Mage_Core_Model_Theme_Customization_Files_CssTest extends PHPUnit_Framework_TestCase
{
    public function testSaveDataWithoutData()
    {
        $filesModel = $this->_getMockThemeFile();
        $themeModel = $this->_getMockThemeModel();

        $modelCssFile = $this->getMock(
            'Mage_Core_Model_Theme_Customization_Files_Css',
            array('_save'),
            array($filesModel)
        );

        $modelCssFile->expects($this->never())->method('_save');
        $modelCssFile->saveData($themeModel);
    }

    public function testSaveData()
    {
        $themeId = 5;
        $cssContent = 'test css content';

        $cssFile = $this->_getMockThemeFile();
        $cssFile->expects($this->once())
            ->method('addData')
            ->with(array(
                'theme_id'  => $themeId,
                'file_path' => 'css/custom.css',
                'file_type' => Mage_Core_Model_Theme_File::TYPE_CSS,
                'content'   => $cssContent
            ))
            ->will($this->returnValue($cssFile));
        $cssFile->expects($this->once())
            ->method('save');

        $filesCollection = $this->_getMockFilesCollection($themeId, $cssFile);

        $filesModel = $this->_getMockThemeFile();
        $filesModel->expects($this->once())->method('getCollection')->will($this->returnValue($filesCollection));

        $themeModel = $this->_getMockThemeModel($themeId);

        $modelCssFile = new Mage_Core_Model_Theme_Customization_Files_Css($filesModel);
        $modelCssFile->setDataForSave(array(Mage_Core_Model_Theme_Customization_Files_Css::CUSTOM_CSS => $cssContent));
        $modelCssFile->saveData($themeModel);
    }

    /**
     * @param int $themeId
     * @param Mage_Core_Model_Theme_File $cssFile
     * @return Mage_Core_Model_Resource_Theme_File_Collection
     */
    protected function _getMockFilesCollection($themeId, $cssFile)
    {
        $filesCollection = $this->getMock(
            'Mage_Core_Model_Resource_Theme_File_Collection', array('addFilter', 'getFirstItem'), array(), '', false
        );
        $filesCollection
            ->expects($this->at(0))
            ->method('addFilter')
            ->with('theme_id', $themeId)
            ->will($this->returnValue($filesCollection));
        $filesCollection
            ->expects($this->at(1))
            ->method('addFilter')
            ->with('file_type', Mage_Core_Model_Theme_File::TYPE_CSS)
            ->will($this->returnValue($filesCollection));
        $filesCollection
            ->expects($this->at(2))
            ->method('addFilter')
            ->with('file_path', 'css/custom.css')
            ->will($this->returnValue($filesCollection));
        $filesCollection
            ->expects($this->once())
            ->method('getFirstItem')
            ->will($this->returnValue($cssFile));

        return $filesCollection;
    }

    /**
     * @param int $return
     * @return PHPUnit_Framework_MockObject_MockObject|Mage_Core_Model_Theme
     */
    protected function _getMockThemeModel($return = null)
    {
        $themeModel = $this->getMock('Mage_Core_Model_Theme', array('getId'), array(), '', false);
        $themeModel->expects($return ? $this->any() : $this->never())
            ->method('getId')
            ->will($this->returnValue($return));
        return $themeModel;
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|Mage_Core_Model_Theme_File
     */
    protected function _getMockThemeFile()
    {
        $filesModel = $this->getMock('Mage_Core_Model_Theme_File', array(
            'addData',
            'save',
            'getCollection'
        ), array(), '', false);
        return $filesModel;
    }
}
