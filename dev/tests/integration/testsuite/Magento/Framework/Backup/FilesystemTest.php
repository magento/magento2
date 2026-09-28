<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Backup;

use \Magento\TestFramework\Helper\Bootstrap;
use \Magento\Framework\App\Filesystem\DirectoryList;

class FilesystemTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\Backup\Filesystem
     */
    private $filesystem;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->filesystem = $this->objectManager->create(\Magento\Framework\Backup\Filesystem::class);
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testRollback()
    {
        $rootDir = Bootstrap::getInstance()->getAppTempDir()
            . '/rollback_test_' . time();
        $backupsDir = __DIR__ . '/_files/var/backups';
        $fileName = 'test.txt';

        mkdir($rootDir);

        $this->filesystem->setRootDir($rootDir)
            ->setBackupsDir($backupsDir)
            ->setTime(1474538269)
            ->setName('code')
            ->setBackupExtension('tgz');

        $this->assertTrue($this->filesystem->rollback());
        $this->assertFileExists($rootDir . '/' . $fileName);
    }
}
