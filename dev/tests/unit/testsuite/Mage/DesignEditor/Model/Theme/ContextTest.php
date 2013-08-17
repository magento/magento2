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
 * @package     Mage_DesignEditor
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_DesignEditor_Model_Theme_ContextTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test theme id
     */
    const THEME_ID = 1;

    /**
     * @var Mage_DesignEditor_Model_Theme_Context
     */
    protected $_model;

    /**
     * @var Mage_Core_Model_ThemeFactory
     */
    protected $_themeFactory;

    /**
     * @var Mage_Core_Helper_Data
     */
    protected $_helper;

    /**
     * @var Mage_Core_Model_Theme_CopyService
     */
    protected $_copyService;

    /**
     * @var Mage_Core_Model_Theme
     */
    protected $_theme;

    public function setUp()
    {
        $this->_themeFactory = $this->getMock('Mage_Core_Model_ThemeFactory', array('create'), array(), '', false);

        $this->_theme = $this->getMock('Mage_Core_Model_Theme',
            array('load', 'getId', 'getType', 'getDomainModel', 'isVirtual'), array(), '', false);
        $this->_themeFactory->expects($this->any())->method('create')->will($this->returnValue($this->_theme));

        $this->_helper = $this->getMock('Mage_Core_Helper_Data', array('__'), array(), '', false);
        $this->_helper->expects($this->any())->method('__')->will($this->returnArgument(0));

        $this->_copyService = $this->getMock('Mage_Core_Model_Theme_CopyService', array('copy'), array(), '', false);

        $this->_model = new Mage_DesignEditor_Model_Theme_Context(
            $this->_themeFactory,
            $this->_helper,
            $this->_copyService
        );
    }

    public function testConstruct()
    {
        $this->assertAttributeEquals($this->_themeFactory, '_themeFactory', $this->_model);
        $this->assertAttributeEquals($this->_helper, '_helper', $this->_model);
        $this->assertAttributeEquals($this->_copyService, '_copyService', $this->_model);
    }

    public function testReset()
    {
        $writersProperty = new ReflectionProperty($this->_model, '_theme');
        $writersProperty->setAccessible(true);
        $writersProperty->setValue($this->_model, new stdClass());
        $this->assertEquals($this->_model, $this->_model->reset());
        $this->assertNull($writersProperty->getValue($this->_model));
    }

    public function testSetEditableThemeById()
    {
        $this->_theme->expects($this->any())
            ->method('load')
            ->with($this->equalTo(self::THEME_ID))
            ->will($this->returnSelf());

        $this->_theme->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(self::THEME_ID));

        $this->_theme->expects($this->any())
            ->method('getType')
            ->will($this->returnValue(Mage_Core_Model_Theme::TYPE_PHYSICAL));

        $this->assertEquals($this->_model, $this->_model->setEditableThemeById(self::THEME_ID));
    }

    /**
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Wrong theme type set as editable
     */
    public function testSetEditableThemeByIdWrongType()
    {
        $this->_theme->expects($this->any())
            ->method('load')
            ->with($this->equalTo(self::THEME_ID))
            ->will($this->returnSelf());

        $this->_theme->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(self::THEME_ID));

        $this->_theme->expects($this->any())
            ->method('getType')
            ->will($this->returnValue(Mage_Core_Model_Theme::TYPE_STAGING));

        $this->_model->setEditableThemeById(self::THEME_ID);
    }

    /**
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage We can't find theme "%s".
     */
    public function testSetEditableThemeByIdWrongThemeId()
    {
        $this->_theme->expects($this->any())
            ->method('load')
            ->with($this->equalTo(self::THEME_ID))
            ->will($this->returnSelf());

        $this->_theme->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(false));

        $this->_model->setEditableThemeById(self::THEME_ID);
    }

    public function testGetEditableTheme()
    {
        $writersProperty = new ReflectionProperty($this->_model, '_theme');
        $writersProperty->setAccessible(true);
        $themeObj = new stdClass();
        $writersProperty->setValue($this->_model, $themeObj);
        $this->assertEquals($themeObj, $this->_model->getEditableTheme());
    }

    /**
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Theme has not been set
     */
    public function testGetEditableThemeNotSet()
    {
        $this->_model->getEditableTheme();
    }

    public function testGetStagingTheme()
    {
        $this->_setEditableTheme();

        $this->_theme->expects($this->atLeastOnce())
            ->method('isVirtual')
            ->will($this->returnValue(true));

        $themeObj = $this->getMock('Mage_Core_Model_Theme_Domain_Virtual', array('getStagingTheme'),
            array(), '', false);
        $themeObj->expects($this->atLeastOnce())->method('getStagingTheme')->will($this->returnSelf());

        $this->_theme->expects($this->atLeastOnce())
            ->method('getDomainModel')
            ->with($this->equalTo(Mage_Core_Model_Theme::TYPE_VIRTUAL))
            ->will($this->returnValue($themeObj));

        $this->assertEquals($themeObj, $this->_model->getStagingTheme());
    }

    public function testGetStagingThemeLazyTest()
    {
        $themeObject = $this->_setStagingTheme();
        $this->assertEquals($themeObject, $this->_model->getStagingTheme());
    }

    /**
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Theme "%s" is not editable.
     */
    public function testGetStagingThemeWrongType()
    {
        $this->_setEditableTheme();

        $this->_theme->expects($this->atLeastOnce())
            ->method('isVirtual')
            ->will($this->returnValue(false));

        $this->_model->getStagingTheme();
    }

    /**
     * @dataProvider themeDataProvider
     */
    public function testGetVisibleTheme($isVirtual)
    {
        $this->_setEditableTheme();
        $this->_theme->expects($this->atLeastOnce())
            ->method('isVirtual')
            ->will($this->returnValue($isVirtual));

        if ($isVirtual) {
            $themeObject = $this->_setStagingTheme();
            $this->assertEquals($themeObject, $this->_model->getVisibleTheme());
        } else {
            $this->assertEquals($this->_theme, $this->_model->getVisibleTheme());
        }
    }

    /**
     * Data Provider for testGetVisibleTheme
     * @return array
     */
    public static function themeDataProvider()
    {
        return array(
            array(true),
            array(false),
        );
    }

    protected function _setEditableTheme()
    {
        $writersProperty = new ReflectionProperty($this->_model, '_theme');
        $writersProperty->setAccessible(true);
        $writersProperty->setValue($this->_model, $this->_theme);
    }

    /**
     * @return stdClass
     */
    protected function _setStagingTheme()
    {
        $writersProperty = new ReflectionProperty($this->_model, '_stagingTheme');
        $writersProperty->setAccessible(true);
        $themeObject = $this->getMock('Mage_Core_Model_Theme', array(), array(), '', false);
        $writersProperty->setValue($this->_model, $themeObject);
        return $themeObject;
    }

    public function testCopyChanges()
    {
        $this->_setEditableTheme();
        $themeObject = $this->_setStagingTheme();
        $this->_copyService->expects($this->atLeastOnce())
            ->method('copy')
            ->with($this->equalTo($themeObject), $this->equalTo($this->_theme))
            ->will($this->returnSelf());
        $this->assertEquals($this->_model, $this->_model->copyChanges());
    }
}
