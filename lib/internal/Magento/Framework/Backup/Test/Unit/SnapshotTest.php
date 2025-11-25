<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Backup\Test\Unit;

use Magento\Framework\Backup\Factory;
use Magento\Framework\Backup\Snapshot;
use Magento\Framework\Filesystem;
use PHPUnit\Framework\TestCase;

class SnapshotTest extends TestCase
{
    public function testGetDbBackupFilename()
    {
        $filesystem = $this->createMock(Filesystem::class);
        $backupFactory = $this->createMock(Factory::class);
        $manager = $this->getMockBuilder(Snapshot::class)
            ->onlyMethods(['getBackupFilename'])
            ->setConstructorArgs([$filesystem, $backupFactory])
            ->getMock();

        $file = 'var/backup/2.sql';
        $manager->expects($this->once())->method('getBackupFilename')->willReturn($file);

        $model = new Snapshot($filesystem, $backupFactory);
        $model->setDbBackupManager($manager);
        $this->assertEquals($file, $model->getDbBackupFilename());
    }
}
