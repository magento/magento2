<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Backup;

class FilesystemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\Backup\Filesystem
     */
    private $filesystem;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->filesystem = $this->objectManager->create(\Magento\Framework\Backup\Filesystem::class);
    }

    public function testRollback()
    {
        $rootDir = __DIR__ . '/_files';
        $backupsDir = $rootDir . '/var/backups';
        $fileName = 'test.txt';

        $this->filesystem->setRootDir($rootDir)
            ->setBackupsDir($backupsDir)
            ->setTime(1474538269)
            ->setName('code')
            ->setBackupExtension('tgz');

        $this->assertTrue($this->filesystem->rollback());
        $this->assertTrue(file_exists($rootDir . '/' . $fileName));
        $this->assertTrue(unlink($rootDir . '/' . $fileName));
    }
}
