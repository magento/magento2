<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Autoload\Test\Unit;

use \Magento\Framework\Autoload\Populator;

use Magento\Framework\App\Filesystem\DirectoryList;

class PopulatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\App\Filesystem\DirectoryList | \PHPUnit_Framework_MockObject_MockObject */
    protected $mockDirectoryList;

    protected function setUp()
    {
        $this->mockDirectoryList = $this->getMockBuilder('\Magento\Framework\App\Filesystem\DirectoryList')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockDirectoryList->expects($this->any())
            ->method('getPath')
            ->willReturnArgument(0);
    }

    public function testPopulateMappings()
    {
        $mockAutoloader = $this->getMockBuilder('\Magento\Framework\Autoload\AutoloaderInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $mockAutoloader->expects($this->at(0))
            ->method('addPsr4')
            ->with(
                'Magento\\',
                [DirectoryList::GENERATION . '/Magento/'],
                true
            );
        $mockAutoloader->expects($this->at(1))
            ->method('addPsr0')
            ->with('Cm_', DirectoryList::LIB_INTERNAL, true);
        $mockAutoloader->expects($this->at(2))
            ->method('addPsr0')
            ->with('Credis_', DirectoryList::LIB_INTERNAL, true);
        $mockAutoloader->expects($this->at(3))
            ->method('addPsr0')
            ->with('', [DirectoryList::GENERATION]);

        Populator::populateMappings($mockAutoloader, $this->mockDirectoryList);
    }
}
