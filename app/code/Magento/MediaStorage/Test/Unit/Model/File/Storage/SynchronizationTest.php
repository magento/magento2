<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaStorage\Test\Unit\Model\File\Storage;

use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\File\Write;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\MediaStorage\Model\File\Storage\Database;
use Magento\MediaStorage\Model\File\Storage\DatabaseFactory;
use Magento\MediaStorage\Model\File\Storage\Synchronization;
use PHPUnit\Framework\TestCase;

class SynchronizationTest extends TestCase
{
    public function testSynchronize()
    {
        $content = 'content';
        $relativeFileName = 'config.xml';

        $storageFactoryMock = $this->getMockBuilder(DatabaseFactory::class)
            ->addMethods(['_wakeup'])
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $storageMock = $this->getMockBuilder(Database::class)
            ->addMethods(['getContent'])
            ->onlyMethods(['getId', 'loadByFilename', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $storageFactoryMock->expects($this->once())->method('create')->willReturn($storageMock);

        $storageMock->expects($this->once())->method('getContent')->willReturn($content);
        $storageMock->expects($this->once())->method('getId')->willReturn(true);
        $storageMock->expects($this->once())->method('loadByFilename');

        $file = $this->createPartialMock(
            Write::class,
            ['lock', 'write', 'unlock', 'close']
        );
        $file->expects($this->once())->method('lock');
        $file->expects($this->once())->method('write')->with($content);
        $file->expects($this->once())->method('unlock');
        $file->expects($this->once())->method('close');
        $directory = $this->getMockForAbstractClass(WriteInterface::class);
        $directory->expects($this->once())
            ->method('openFile')
            ->with($relativeFileName)
            ->willReturn($file);

        $objectManager = new ObjectManager($this);
        $model = $objectManager->getObject(Synchronization::class, [
            'storageFactory' => $storageFactoryMock,
            'directory' => $directory,
        ]);
        $model->synchronize($relativeFileName);
    }
}
