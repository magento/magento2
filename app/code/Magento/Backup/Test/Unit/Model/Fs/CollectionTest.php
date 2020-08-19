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

        $directoryWrite->expects($this->any())->method('create')->with('backups');
        $directoryWrite->expects($this->any())->method('getAbsolutePath')->with('backups');

        $classObject = $helper->getObject(
            Collection::class,
            ['filesystem' => $filesystem, 'backupData' => $backupData]
        );
        $this->assertNotNull($classObject);
    }
}
