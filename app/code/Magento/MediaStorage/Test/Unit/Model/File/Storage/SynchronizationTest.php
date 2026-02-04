<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\MediaStorage\Test\Unit\Model\File\Storage;

use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\File\Write;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\MediaStorage\Model\File\Storage\Database;
use Magento\MediaStorage\Model\File\Storage\DatabaseFactory;
use Magento\MediaStorage\Model\File\Storage\Synchronization;
use PHPUnit\Framework\TestCase;

class SynchronizationTest extends TestCase
{
    use MockCreationTrait;
    public function testSynchronize(): void
    {
        $content = 'content';
        $relativeFileName = 'config.xml';

        $storageFactoryMock = $this->createPartialMock(DatabaseFactory::class, ['create']);

        $storageMock = $this->createPartialMockWithReflection(
            Database::class,
            ['loadByFilename', 'getId', 'getContent']
        );
        $reflection = new \ReflectionClass($storageMock);
        $dataProperty = $reflection->getProperty('_data');
        $dataProperty->setValue($storageMock, [
            'id' => true,
            'content' => $content
        ]);

        $storageMock->expects($this->once())->method('loadByFilename');
        $storageMock->expects($this->once())->method('getId')->willReturn(true);
        $storageMock->expects($this->once())->method('getContent')->willReturn($content);

        $storageFactoryMock->expects($this->once())->method('create')->willReturn($storageMock);

        $file = $this->createPartialMock(
            Write::class,
            ['lock', 'write', 'unlock', 'close']
        );
        $file->expects($this->once())->method('lock');
        $file->expects($this->once())->method('write')->with($content);
        $file->expects($this->once())->method('unlock');
        $file->expects($this->once())->method('close');
        $directory = $this->createMock(WriteInterface::class);
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
