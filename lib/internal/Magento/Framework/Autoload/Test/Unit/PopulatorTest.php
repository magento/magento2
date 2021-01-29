<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Autoload\Test\Unit;

use \Magento\Framework\Autoload\Populator;

use Magento\Framework\App\Filesystem\DirectoryList;

class PopulatorTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\App\Filesystem\DirectoryList | \PHPUnit\Framework\MockObject\MockObject */
    protected $mockDirectoryList;

    protected function setUp(): void
    {
        $this->mockDirectoryList = $this->getMockBuilder(\Magento\Framework\App\Filesystem\DirectoryList::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockDirectoryList->expects($this->any())
            ->method('getPath')
            ->willReturnArgument(0);
    }

    public function testPopulateMappings()
    {
        $mockAutoloader = $this->getMockBuilder(\Magento\Framework\Autoload\AutoloaderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockAutoloader->expects($this->once())
            ->method('addPsr4')
            ->with(
                'Magento\\',
                [DirectoryList::GENERATED_CODE . '/Magento/'],
                true
            );
        $mockAutoloader->expects($this->once())
            ->method('addPsr0')
            ->with('', [DirectoryList::GENERATED_CODE]);

        Populator::populateMappings($mockAutoloader, $this->mockDirectoryList);
    }
}
