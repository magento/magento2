<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MediaStorage\Test\Unit\Model\File\Storage;

class SynchronizationTest extends \PHPUnit_Framework_TestCase
{
    public function testSynchronize()
    {
        $content = 'content';
        $relativeFileName = 'config.xml';

        $storageFactoryMock = $this->getMock(
            'Magento\MediaStorage\Model\File\Storage\DatabaseFactory',
            ['create', '_wakeup'],
            [],
            '',
            false
        );
        $storageMock = $this->getMock(
            'Magento\MediaStorage\Model\File\Storage\Database',
            ['getContent', 'getId', 'loadByFilename', '__wakeup'],
            [],
            '',
            false
        );
        $storageFactoryMock->expects($this->once())->method('create')->will($this->returnValue($storageMock));

        $storageMock->expects($this->once())->method('getContent')->will($this->returnValue($content));
        $storageMock->expects($this->once())->method('getId')->will($this->returnValue(true));
        $storageMock->expects($this->once())->method('loadByFilename');

        $file = $this->getMock(
            'Magento\Framework\Filesystem\File\Write',
            ['lock', 'write', 'unlock', 'close'],
            [],
            '',
            false
        );
        $file->expects($this->once())->method('lock');
        $file->expects($this->once())->method('write')->with($content);
        $file->expects($this->once())->method('unlock');
        $file->expects($this->once())->method('close');
        $directory = $this->getMockForAbstractClass('Magento\Framework\Filesystem\Directory\WriteInterface');
        $directory->expects($this->once())
            ->method('openFile')
            ->with($relativeFileName)
            ->will($this->returnValue($file));

        $model = new \Magento\MediaStorage\Model\File\Storage\Synchronization($storageFactoryMock, $directory);
        $model->synchronize($relativeFileName);
    }
}
