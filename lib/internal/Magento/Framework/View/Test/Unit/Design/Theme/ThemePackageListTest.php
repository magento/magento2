<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Design\Theme;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\View\Design\Theme\ThemePackageList;

class ThemePackageListTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Component\ComponentRegistrarInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $registrar;

    /**
     * @var ThemePackageList
     */
    private $object;

    /**
     * @var \Magento\Framework\View\Design\Theme\ThemePackageFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $factory;

    protected function setUp(): void
    {
        $this->registrar = $this->getMockForAbstractClass(
            \Magento\Framework\Component\ComponentRegistrarInterface::class
        );
        $this->factory = $this->createMock(\Magento\Framework\View\Design\Theme\ThemePackageFactory::class);
        $this->object = new ThemePackageList($this->registrar, $this->factory);
    }

    /**
     */
    public function testGetThemeNonexistent()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('No theme registered with name \'theme\'');

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
        $themePackage = $this->createMock(\Magento\Framework\View\Design\Theme\ThemePackage::class);
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
        $themePackage = $this->createMock(\Magento\Framework\View\Design\Theme\ThemePackage::class);
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
