<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backup\Test\Unit\Model\Fs;

use Magento\Backup\Helper\Data;
use Magento\Backup\Model\Fs\Collection;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\TargetDirectory;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    public function testConstructor()
    {
        $helper = new ObjectManager($this);
        $filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $directoryWrite = $this->getMockBuilder(
            WriteInterface::class
        )->disableOriginalConstructor()
            ->getMock();
        $filesystem->expects($this->any())->method('getDirectoryWrite')->willReturn($directoryWrite);

        $backupData = $this->getMockBuilder(
            Data::class
        )->disableOriginalConstructor()
            ->getMock();
        $backupData->expects($this->any())->method('getExtensions')->willReturn([]);
        $driver = $this->getMockBuilder(
            Filesystem\DriverInterface::class
        )->disableOriginalConstructor()
            ->getMock();
        $directoryWrite->expects($this->any())->method('create')->with('backups');
        $directoryWrite->expects($this->any())->method('getAbsolutePath')->willReturn('');
        $directoryWrite->expects($this->at(3))->method('getAbsolutePath')->with('backups');
        $directoryWrite->expects($this->any())->method('isDirectory')->willReturn(true);
        $directoryWrite->expects($this->any())->method('getDriver')->willReturn($driver);
        $targetDirectory = $this->getMockBuilder(TargetDirectory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $targetDirectoryWrite = $this->getMockBuilder(WriteInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $targetDirectoryWrite->expects($this->any())->method('isDirectory')->willReturn(true);
        $targetDirectory->expects($this->any())->method('getDirectoryWrite')->willReturn($targetDirectoryWrite);
        $classObject = $helper->getObject(
            Collection::class,
            [
                'filesystem' => $filesystem,
                'backupData' => $backupData,
                'directoryWrite' => $directoryWrite,
                'targetDirectory' => $targetDirectory
            ]
        );
        $this->assertNotNull($classObject);
    }
}
