<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * @var \Magento\Theme\Model\Theme\Data\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collection;

    /**
     * @var ThemePackageInfo
     */
    private $themePackageInfo;

    public function setUp()
    {
        $filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $this->dirRead = $this->getMock('Magento\Framework\Filesystem\Directory\Read', [], [], '', false);
        $this->collection = $this->getMock('Magento\Theme\Model\Theme\Data\Collection', [], [], '', false);
        $filesystem->expects($this->once())->method('getDirectoryRead')->willReturn($this->dirRead);
        $this->themePackageInfo = new ThemePackageInfo($this->collection, $filesystem);
    }

    public function testGetPackageName()
    {
        $this->dirRead->expects($this->once())->method('isExist')->with('themeA/composer.json')->willReturn(true);
        $this->dirRead->expects($this->once())
            ->method('readFile')
            ->with('themeA/composer.json')
            ->willReturn('{"name": "package"}');
        $this->assertEquals('package', $this->themePackageInfo->getPackageName('themeA'));
    }

    public function testGetPackageNameNonExist()
    {
        $this->dirRead->expects($this->once())->method('isExist')->with('themeA/composer.json')->willReturn(false);
        $this->dirRead->expects($this->never())->method('readFile')->with('themeA/composer.json');
        $this->assertEquals('', $this->themePackageInfo->getPackageName('themeA'));
    }

    public function testGetFullThemePath()
    {
        $this->collection->expects($this->once())->method('addDefaultPattern')->with('*');
        $theme = $this->getMock('Magento\Theme\Model\Theme', [], [], '', false);
        $theme->expects($this->once())->method('getFullPath')->willReturn('fullPath');
        $this->collection->expects($this->once())->method('getIterator')->willReturn([$theme]);
        $this->dirRead->expects($this->once())->method('isExist')->willReturn(true);
        $this->dirRead->expects($this->once())->method('readFile')->willReturn('{"name": "package"}');
        $this->assertEquals('fullPath', $this->themePackageInfo->getFullThemePath('package'));
        // call one more time to make sure only initialize once
        $this->assertEquals('fullPath', $this->themePackageInfo->getFullThemePath('package'));
    }

    public function testGetFullThemePathNonExist()
    {
        $this->collection->expects($this->once())->method('addDefaultPattern')->with('*');
        $this->collection->expects($this->once())->method('getIterator')->willReturn([]);
        $this->assertEquals('', $this->themePackageInfo->getFullThemePath('package'));
    }

    /**
     * @expectedException \Zend_Json_Exception
     */
    public function testGetPackageNameInvalidJson()
    {
        $this->dirRead->expects($this->once())->method('isExist')->willReturn(true);
        $this->dirRead->expects($this->once())->method('readFile')->willReturn('{"name": }');
        $this->assertEquals('package', $this->themePackageInfo->getPackageName('themeA'));
    }
}
