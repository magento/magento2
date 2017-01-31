<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MediaStorage\Test\Unit\Model\File\Storage;

use Magento\Framework\App\Filesystem\DirectoryList;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test for save method
     */
    public function testSave()
    {
        $config = [];
        $fileStorageMock = $this->getMock('Magento\MediaStorage\Model\File\Storage', [], [], '', false);
        $fileStorageMock->expects($this->once())->method('getScriptConfig')->will($this->returnValue($config));

        $file = $this->getMock(
            'Magento\Framework\Filesystem\File\Write',
            ['lock', 'write', 'unlock', 'close'],
            [],
            '',
            false
        );
        $file->expects($this->once())->method('lock');
        $file->expects($this->once())->method('write')->with(json_encode($config));
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
        $directory->expects($this->once())->method('openFile')->with('cacheFile')->will($this->returnValue($file));
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
            DirectoryList::ROOT
        )->will(
            $this->returnValue($directory)
        );
        $model = new \Magento\MediaStorage\Model\File\Storage\Config($fileStorageMock, $filesystem, 'cacheFile');
        $model->save();
    }
}
