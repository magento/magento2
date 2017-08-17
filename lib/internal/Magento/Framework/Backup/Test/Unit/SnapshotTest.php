<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Backup\Test\Unit;

class SnapshotTest extends \PHPUnit\Framework\TestCase
{
    public function testGetDbBackupFilename()
    {
        $filesystem = $this->createMock(\Magento\Framework\Filesystem::class);
        $backupFactory = $this->createMock(\Magento\Framework\Backup\Factory::class);
        $manager = $this->getMockBuilder(\Magento\Framework\Backup\Snapshot::class)
            ->setMethods(['getBackupFilename'])
            ->setConstructorArgs([$filesystem, $backupFactory])
            ->getMock();

        $file = 'var/backup/2.sql';
        $manager->expects($this->once())->method('getBackupFilename')->will($this->returnValue($file));

        $model = new \Magento\Framework\Backup\Snapshot($filesystem, $backupFactory);
        $model->setDbBackupManager($manager);
        $this->assertEquals($file, $model->getDbBackupFilename());
    }
}
