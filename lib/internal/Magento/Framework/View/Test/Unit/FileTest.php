<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit;

class FileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\File
     */
    private $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_theme;

    protected function setUp()
    {
        $this->_theme = $this->getMockForAbstractClass('Magento\Framework\View\Design\ThemeInterface');
        $this->_model = new \Magento\Framework\View\File(__FILE__, 'Fixture_TestModule', $this->_theme, true);
    }

    public function testGetFilename()
    {
        $this->assertEquals(__FILE__, $this->_model->getFilename());
    }

    public function testGetName()
    {
        $this->assertEquals('FileTest.php', $this->_model->getName());
    }

    public function testGetModule()
    {
        $this->assertEquals('Fixture_TestModule', $this->_model->getModule());
    }

    public function testGetTheme()
    {
        $this->assertSame($this->_theme, $this->_model->getTheme());
    }

    public function testGetFileIdentifier()
    {
        $this->_theme->expects($this->once())->method('getFullPath')->will($this->returnValue('theme_name'));
        $this->assertSame(
            'base|theme:theme_name|module:Fixture_TestModule|file:FileTest.php',
            $this->_model->getFileIdentifier()
        );
    }
}
