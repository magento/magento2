<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Backup\Test\Unit;

class SnapshotTest extends \PHPUnit_Framework_TestCase
{
    public function testGetDbBackupFilename()
    {
        $filesystem = $this->getMock(\Magento\Framework\Filesystem::class, [], [], '', false);
        $backupFactory = $this->getMock(\Magento\Framework\Backup\Factory::class, [], [], '', false);
        $manager = $this->getMock(
            \Magento\Framework\Backup\Snapshot::class,
            ['getBackupFilename'],
            [$filesystem, $backupFactory]
        );

        $file = 'var/backup/2.sql';
        $manager->expects($this->once())->method('getBackupFilename')->will($this->returnValue($file));

        $model = new \Magento\Framework\Backup\Snapshot($filesystem, $backupFactory);
        $model->setDbBackupManager($manager);
        $this->assertEquals($file, $model->getDbBackupFilename());
    }
}
