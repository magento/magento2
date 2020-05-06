<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\View\Deployment\Version\Storage;

use Magento\Framework\App\View\Deployment\Version\Storage\File;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FileTest extends TestCase
{
    /**
     * @var File
     */
    private $object;

    /**
     * @var MockObject
     */
    private $directory;

    protected function setUp(): void
    {
        $this->directory = $this->getMockForAbstractClass(WriteInterface::class);
        $filesystem = $this->createMock(Filesystem::class);
        $filesystem
            ->expects($this->once())
            ->method('getDirectoryWrite')
            ->with('fixture_dir')
            ->willReturn($this->directory);
        $this->object = new File($filesystem, 'fixture_dir', 'fixture_file.txt');
    }

    public function testLoad()
    {
        $this->directory->expects($this->once())
            ->method('isReadable')
            ->with('fixture_file.txt')
            ->willReturn(true);
        $this->directory->expects($this->once())
            ->method('readFile')
            ->with('fixture_file.txt')
            ->willReturn('123');
        $this->assertEquals('123', $this->object->load());
    }

    public function testSave()
    {
        $this->directory
            ->expects($this->once())
            ->method('writeFile')
            ->with('fixture_file.txt', 'input_data', 'w');
        $this->object->save('input_data');
    }
}
