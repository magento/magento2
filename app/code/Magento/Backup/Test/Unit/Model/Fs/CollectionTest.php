<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backup\Test\Unit\Model\Fs;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $filesystem = $this->getMockBuilder('\Magento\Framework\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();
        $directoryWrite = $this->getMockBuilder(
            '\Magento\Framework\Filesystem\Directory\WriteInterface'
        )->disableOriginalConstructor()->getMock();
        $filesystem->expects($this->any())->method('getDirectoryWrite')->will($this->returnValue($directoryWrite));

        $backupData = $this->getMockBuilder('\Magento\Backup\Helper\Data')->disableOriginalConstructor()->getMock();
        $backupData->expects($this->any())->method('getExtensions')->will($this->returnValue([]));

        $directoryWrite->expects($this->any())->method('create')->with('backups');
        $directoryWrite->expects($this->any())->method('getAbsolutePath')->with('backups');

        $helper->getObject(
            'Magento\Backup\Model\Fs\Collection',
            ['filesystem' => $filesystem, 'backupData' => $backupData]
        );
    }
}
