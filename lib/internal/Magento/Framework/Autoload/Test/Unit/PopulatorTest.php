<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Autoload\Test\Unit;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Autoload\AutoloaderInterface;
use Magento\Framework\Autoload\Populator;
use PHPUnit\Framework\MockObject\MockObject;

use PHPUnit\Framework\TestCase;

class PopulatorTest extends TestCase
{
    /** @var DirectoryList|MockObject */
    protected $mockDirectoryList;

    protected function setUp(): void
    {
        $this->mockDirectoryList = $this->getMockBuilder(DirectoryList::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockDirectoryList->expects($this->any())
            ->method('getPath')
            ->willReturnArgument(0);
    }

    public function testPopulateMappings()
    {
        $mockAutoloader = $this->getMockBuilder(AutoloaderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

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
