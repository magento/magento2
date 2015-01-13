<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Model\Theme;

class ContextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test theme id
     */
    const THEME_ID = 1;

    /**
     * @var \Magento\DesignEditor\Model\Theme\Context
     */
    protected $_model;

    /**
     * @var \Magento\Core\Model\ThemeFactory
     */
    protected $_themeFactory;

    /**
     * @var \Magento\Theme\Model\CopyService
     */
    protected $_copyService;

    /**
     * @var \Magento\Core\Model\Theme
     */
    protected $_theme;

    protected function setUp()
    {
        $this->_themeFactory = $this->getMock('Magento\Core\Model\ThemeFactory', ['create'], [], '', false);

        $this->_theme = $this->getMock(
            'Magento\Core\Model\Theme',
            ['load', 'getId', 'getType', 'getDomainModel', 'isVirtual', '__wakeup'],
            [],
            '',
            false
        );
        $this->_themeFactory->expects($this->any())->method('create')->will($this->returnValue($this->_theme));

        $this->_copyService = $this->getMock('Magento\Theme\Model\CopyService', ['copy'], [], '', false);

        $this->_model = new \Magento\DesignEditor\Model\Theme\Context($this->_themeFactory, $this->_copyService);
    }

    public function testConstruct()
    {
        $this->assertAttributeEquals($this->_themeFactory, '_themeFactory', $this->_model);
        $this->assertAttributeEquals($this->_copyService, '_copyService', $this->_model);
    }

    public function testReset()
    {
        $writersProperty = new \ReflectionProperty($this->_model, '_theme');
        $writersProperty->setAccessible(true);
        $writersProperty->setValue($this->_model, new \stdClass());
        $this->assertEquals($this->_model, $this->_model->reset());
        $this->assertNull($writersProperty->getValue($this->_model));
    }

    public function testSetEditableThemeById()
    {
        $this->_theme->expects(
            $this->any()
        )->method(
            'load'
        )->with(
            $this->equalTo(self::THEME_ID)
        )->will(
            $this->returnSelf()
        );

        $this->_theme->expects($this->any())->method('getId')->will($this->returnValue(self::THEME_ID));

        $this->_theme->expects(
            $this->any()
        )->method(
            'getType'
        )->will(
            $this->returnValue(\Magento\Framework\View\Design\ThemeInterface::TYPE_PHYSICAL)
        );

        $this->assertEquals($this->_model, $this->_model->setEditableThemeById(self::THEME_ID));
    }

    /**
     * @expectedException \Magento\Framework\Model\Exception
     * @expectedExceptionMessage Wrong theme type set as editable
     */
    public function testSetEditableThemeByIdWrongType()
    {
        $this->_theme->expects(
            $this->any()
        )->method(
            'load'
        )->with(
            $this->equalTo(self::THEME_ID)
        )->will(
            $this->returnSelf()
        );

        $this->_theme->expects($this->any())->method('getId')->will($this->returnValue(self::THEME_ID));

        $this->_theme->expects(
            $this->any()
        )->method(
            'getType'
        )->will(
            $this->returnValue(\Magento\Framework\View\Design\ThemeInterface::TYPE_STAGING)
        );

        $this->_model->setEditableThemeById(self::THEME_ID);
    }

    /**
     * @expectedException \Magento\Framework\Model\Exception
     * @expectedExceptionMessage We can't find theme "1".
     */
    public function testSetEditableThemeByIdWrongThemeId()
    {
        $this->_theme->expects(
            $this->any()
        )->method(
            'load'
        )->with(
            $this->equalTo(self::THEME_ID)
        )->will(
            $this->returnSelf()
        );

        $this->_theme->expects($this->any())->method('getId')->will($this->returnValue(false));

        $this->_model->setEditableThemeById(self::THEME_ID);
    }

    public function testGetEditableTheme()
    {
        $writersProperty = new \ReflectionProperty($this->_model, '_theme');
        $writersProperty->setAccessible(true);
        $themeObj = new \stdClass();
        $writersProperty->setValue($this->_model, $themeObj);
        $this->assertEquals($themeObj, $this->_model->getEditableTheme());
    }

    /**
     * @expectedException \Magento\Framework\Model\Exception
     * @expectedExceptionMessage Theme has not been set
     */
    public function testGetEditableThemeNotSet()
    {
        $this->_model->getEditableTheme();
    }

    public function testGetStagingTheme()
    {
        $this->_setEditableTheme();

        $this->_theme->expects($this->atLeastOnce())->method('isVirtual')->will($this->returnValue(true));

        $themeObj = $this->getMock(
            'Magento\Core\Model\Theme\Domain\Virtual',
            ['getStagingTheme'],
            [],
            '',
            false
        );
        $themeObj->expects($this->atLeastOnce())->method('getStagingTheme')->will($this->returnSelf());

        $this->_theme->expects(
            $this->atLeastOnce()
        )->method(
            'getDomainModel'
        )->with(
            $this->equalTo(\Magento\Framework\View\Design\ThemeInterface::TYPE_VIRTUAL)
        )->will(
            $this->returnValue($themeObj)
        );

        $this->assertEquals($themeObj, $this->_model->getStagingTheme());
    }

    public function testGetStagingThemeLazyTest()
    {
        $themeObject = $this->_setStagingTheme();
        $this->assertEquals($themeObject, $this->_model->getStagingTheme());
    }

    /**
     * @expectedException \Magento\Framework\Model\Exception
     * @expectedExceptionMessage Theme "" is not editable.
     */
    public function testGetStagingThemeWrongType()
    {
        $this->_setEditableTheme();

        $this->_theme->expects($this->atLeastOnce())->method('isVirtual')->will($this->returnValue(false));

        $this->_model->getStagingTheme();
    }

    /**
     * @dataProvider themeDataProvider
     */
    public function testGetVisibleTheme($isVirtual)
    {
        $this->_setEditableTheme();
        $this->_theme->expects($this->atLeastOnce())->method('isVirtual')->will($this->returnValue($isVirtual));

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
        return [[true], [false]];
    }

    protected function _setEditableTheme()
    {
        $writersProperty = new \ReflectionProperty($this->_model, '_theme');
        $writersProperty->setAccessible(true);
        $writersProperty->setValue($this->_model, $this->_theme);
    }

    /**
     * @return \stdClass
     */
    protected function _setStagingTheme()
    {
        $writersProperty = new \ReflectionProperty($this->_model, '_stagingTheme');
        $writersProperty->setAccessible(true);
        $themeObject = $this->getMock('Magento\Framework\View\Design\ThemeInterface', [], [], '', false);
        $writersProperty->setValue($this->_model, $themeObject);
        return $themeObject;
    }

    public function testCopyChanges()
    {
        $this->_setEditableTheme();
        $themeObject = $this->_setStagingTheme();
        $this->_copyService->expects(
            $this->atLeastOnce()
        )->method(
            'copy'
        )->with(
            $this->equalTo($themeObject),
            $this->equalTo($this->_theme)
        )->will(
            $this->returnSelf()
        );
        $this->assertEquals($this->_model, $this->_model->copyChanges());
    }
}
