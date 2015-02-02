<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Core\Model\File\Storage;

use Magento\Framework\App\Filesystem\DirectoryList;

class SynchronizationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test fir synchronize method
     */
    public function testSynchronize()
    {
        $content = 'content';
        $relativeFileName = 'config.xml';
        $filePath = realpath(__DIR__ . '/_files/');

        $storageFactoryMock = $this->getMock(
            'Magento\Core\Model\File\Storage\DatabaseFactory',
            ['create', '_wakeup'],
            [],
            '',
            false
        );
        $storageMock = $this->getMock(
            'Magento\Core\Model\File\Storage\Database',
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
        $directory = $this->getMock(
            'Magento\Framework\Filesystem\Direcoty\Write',
            ['openFile', 'getRelativePath'],
            [],
            '',
            false
        );
        $directory->expects($this->once())->method('getRelativePath')->will($this->returnArgument(0));
        $directory->expects($this->once())->method('openFile')->with($filePath)->will($this->returnValue($file));
        $filesystem = $this->getMock(
            'Magento\Framework\Filesystem',
            ['getDirectoryWrite'],
            [],
            '',
            false
        );
        $filesystem->expects(
            $this->once()
        )->method(
            'getDirectoryWrite'
        )->with(
            DirectoryList::PUB
        )->will(
            $this->returnValue($directory)
        );

        $model = new Synchronization($storageFactoryMock, $filesystem);
        $model->synchronize($relativeFileName, $filePath);
    }
}
