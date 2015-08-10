<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\Theme;

use Magento\Theme\Model\Theme\PackageNameFinder;

class PackageNameFinderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\Read|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dirRead;

    /**
     * @var PackageNameFinder
     */
    private $packageNameFinder;

    public function setUp()
    {
        $filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $this->dirRead = $this->getMock('Magento\Framework\Filesystem\Directory\Read', [], [], '', false);
        $filesystem->expects($this->once())->method('getDirectoryRead')->willReturn($this->dirRead);
        $this->packageNameFinder = new PackageNameFinder($filesystem);
    }

    public function testGetPackageName()
    {
        $this->dirRead->expects($this->once())->method('isExist')->willReturn(true);
        $this->dirRead->expects($this->once())->method('readFile')->willReturn('{"name": "package"}');
        $this->assertEquals('package', $this->packageNameFinder->getPackageName('themeA'));
    }

    public function testGetPackageNameNonExist()
    {
        $this->dirRead->expects($this->once())->method('isExist')->willReturn(false);
        $this->dirRead->expects($this->never())->method('readFile');
        $this->assertEquals('', $this->packageNameFinder->getPackageName('themeA'));
    }

    /**
     * @expectedException \Zend_Json_Exception
     */
    public function testGetPackageNameInvalidJson()
    {
        $this->dirRead->expects($this->once())->method('isExist')->willReturn(true);
        $this->dirRead->expects($this->once())->method('readFile')->willReturn('{"name": }');
        $this->assertEquals('package', $this->packageNameFinder->getPackageName('themeA'));
    }
}
