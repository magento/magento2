<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\Theme;

use Magento\Theme\Model\Theme\ThemePackageInfo;

class ThemePackageInfoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\Read|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dirRead;

    /**
     * @var ThemePackageInfo
     */
    private $themePackageInfo;

    /**
     * @var \Magento\Framework\Component\ComponentRegistrar|\PHPUnit_Framework_MockObject_MockObject
     */
    private $componentRegistrar;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dirReadFactory;

    protected function setUp()
    {
        $this->componentRegistrar = $this->getMock('Magento\Framework\Component\ComponentRegistrar', [], [], '', false);
        $this->dirRead = $this->getMock('Magento\Framework\Filesystem\Directory\Read', [], [], '', false);
        $this->dirReadFactory = $this->getMock('Magento\Framework\Filesystem\Directory\ReadFactory', [], [], '', false);
        $this->dirReadFactory->expects($this->any())->method('create')->willReturn($this->dirRead);
        $this->themePackageInfo = new ThemePackageInfo(
            $this->componentRegistrar,
            $this->dirReadFactory
        );
    }

    public function testGetPackageName()
    {
        $this->componentRegistrar->expects($this->once())->method('getPath')->willReturn('path/to/A');
        $this->dirRead->expects($this->once())->method('isExist')->with('composer.json')->willReturn(true);
        $this->dirRead->expects($this->once())
            ->method('readFile')
            ->with('composer.json')
            ->willReturn('{"name": "package"}');
        $this->assertEquals('package', $this->themePackageInfo->getPackageName('themeA'));
    }

    public function testGetPackageNameNonExist()
    {
        $this->componentRegistrar->expects($this->once())->method('getPath')->willReturn('path/to/A');
        $this->dirRead->expects($this->once())->method('isExist')->with('composer.json')->willReturn(false);
        $this->dirRead->expects($this->never())->method('readFile')->with('composer.json');
        $this->assertEquals('', $this->themePackageInfo->getPackageName('themeA'));
    }

    public function testGetFullThemePath()
    {
        $this->componentRegistrar->expects($this->once())->method('getPaths')->willReturn(['themeA' => 'path/to/A']);
        $this->dirRead->expects($this->once())->method('isExist')->willReturn(true);
        $this->dirRead->expects($this->once())->method('readFile')->willReturn('{"name": "package"}');
        $this->assertEquals('themeA', $this->themePackageInfo->getFullThemePath('package'));
        // call one more time to make sure only initialize once
        $this->assertEquals('themeA', $this->themePackageInfo->getFullThemePath('package'));
    }

    public function testGetFullThemePathNonExist()
    {
        $this->componentRegistrar->expects($this->once())->method('getPaths')->willReturn(['themeA' => 'path/to/A']);
        $this->dirRead->expects($this->once())->method('isExist')->willReturn(true);
        $this->dirRead->expects($this->once())->method('readFile')->willReturn('{"name": "package"}');
        $this->assertEquals('', $this->themePackageInfo->getFullThemePath('package-other'));
    }

    /**
     * @expectedException \Zend_Json_Exception
     */
    public function testGetPackageNameInvalidJson()
    {
        $this->componentRegistrar->expects($this->once())->method('getPath')->willReturn('path/to/A');
        $this->dirRead->expects($this->once())->method('isExist')->willReturn(true);
        $this->dirRead->expects($this->once())->method('readFile')->willReturn('{"name": }');
        $this->themePackageInfo->getPackageName('themeA');
    }
}
