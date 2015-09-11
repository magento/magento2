<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Autoload\Test\Unit;

use \Magento\Framework\Autoload\Populator;

use Magento\Framework\App\Filesystem\DirectoryList;

class PopulatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\App\Filesystem\DirectoryList | \PHPUnit_Framework_MockObject_MockObject */
    protected $mockDirectoryList;

    /**
     * @var \Magento\Framework\Component\ComponentRegistrar|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $componentRegistrar;

    public function setUp()
    {
        $this->mockDirectoryList = $this->getMockBuilder('\Magento\Framework\App\Filesystem\DirectoryList')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockDirectoryList->expects($this->any())
            ->method('getPath')
            ->willReturnArgument(0);

        $this->componentRegistrar = $this->getMock('Magento\Framework\Component\ComponentRegistrar', [], [], '', false);
    }

    public function testPopulateMappings()
    {
        $mockAutoloader = $this->getMockBuilder('\Magento\Framework\Autoload\AutoloaderInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $mockAutoloader->expects($this->at(0))->method('addPsr4')->with('Magento\\A\\', ['/path/to/a/'], true);
        $mockAutoloader->expects($this->at(1))->method('addPsr4')->with('Magento\\B\\', ['/path/to/b/'], true);
        $mockAutoloader->expects($this->at(2))->method('addPsr4')->with('Magento\\C\\', ['/path/to/c/'], true);
        $mockAutoloader->expects($this->at(3))
            ->method('addPsr4')
            ->with('Magento\\', [DirectoryList::GENERATION . '/Magento/'], true);
        $mockAutoloader->expects($this->at(4))
            ->method('addPsr0')
            ->with('Apache_', DirectoryList::LIB_INTERNAL, true);
        $mockAutoloader->expects($this->at(5))
            ->method('addPsr0')
            ->with('Cm_', DirectoryList::LIB_INTERNAL, true);
        $mockAutoloader->expects($this->at(6))
            ->method('addPsr0')
            ->with('Credis_', DirectoryList::LIB_INTERNAL, true);
        $mockAutoloader->expects($this->at(7))
            ->method('addPsr0')
            ->with('Less_', DirectoryList::LIB_INTERNAL, true);
        $mockAutoloader->expects($this->at(8))
            ->method('addPsr0')
            ->with('Symfony\\', DirectoryList::LIB_INTERNAL, true);
        $mockAutoloader->expects($this->at(9))
            ->method('addPsr0')
            ->with('', [DirectoryList::GENERATION]);

        $moduleDirs = [
            'Magento_A' => '/path/to/a',
            'Magento_B' => '/path/to/b',
            'Magento_C' => '/path/to/c',
        ];
        $this->componentRegistrar->expects($this->once())->method('getPaths')->willReturn($moduleDirs);

        Populator::populateMappings($mockAutoloader, $this->mockDirectoryList, $this->componentRegistrar);
    }
}
