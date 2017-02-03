<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Design\Theme;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\View\Design\Theme\ThemePackageList;

class ThemePackageListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Component\ComponentRegistrarInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $registrar;

    /**
     * @var ThemePackageList
     */
    private $object;

    /**
     * @var \Magento\Framework\View\Design\Theme\ThemePackageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $factory;

    protected function setUp()
    {
        $this->registrar = $this->getMockForAbstractClass('\Magento\Framework\Component\ComponentRegistrarInterface');
        $this->factory = $this->getMock('Magento\Framework\View\Design\Theme\ThemePackageFactory', [], [], '', false);
        $this->object = new ThemePackageList($this->registrar, $this->factory);
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage No theme registered with name 'theme'
     */
    public function testGetThemeNonexistent()
    {
        $themeKey = 'theme';
        $this->registrar->expects($this->once())
            ->method('getPath')
            ->with(ComponentRegistrar::THEME, $themeKey)
            ->willReturn(null);
        $this->factory->expects($this->never())
            ->method('create');
        $this->object->getTheme($themeKey);
    }

    public function testGetTheme()
    {
        $themeKey = 'theme';
        $themePath = 'path';
        $this->registrar->expects($this->once())
            ->method('getPath')
            ->with(ComponentRegistrar::THEME, $themeKey)
            ->willReturn($themePath);
        $themePackage = $this->getMock('\Magento\Framework\View\Design\Theme\ThemePackage', [], [], '', false);
        $this->factory->expects($this->once())
            ->method('create')
            ->with($themeKey, $themePath)
            ->willReturn($themePackage);
        $this->assertSame($themePackage, $this->object->getTheme($themeKey));
    }

    public function testGetThemes()
    {
        $this->registrar->expects($this->once())
            ->method('getPaths')
            ->with(ComponentRegistrar::THEME)
            ->willReturn(['theme1' => 'path1', 'theme2' => 'path2']);
        $themePackage = $this->getMock('\Magento\Framework\View\Design\Theme\ThemePackage', [], [], '', false);
        $this->factory->expects($this->exactly(2))
            ->method('create')
            ->withConsecutive(
                ['theme1', 'path1'],
                ['theme2', 'path2']
            )
            ->willReturn($themePackage);
        $actual = $this->object->getThemes();
        $this->assertCount(2, $actual);
        foreach ($actual as $themePackage) {
            $this->assertSame($themePackage, $themePackage);
        }
    }
}
