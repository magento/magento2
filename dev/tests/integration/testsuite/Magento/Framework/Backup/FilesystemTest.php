<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Backup;

use \Magento\TestFramework\Helper\Bootstrap;
use \Magento\Framework\App\Filesystem\DirectoryList;

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
        $this->objectManager = Bootstrap::getObjectManager();
        $this->filesystem = $this->objectManager->create(\Magento\Framework\Backup\Filesystem::class);
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testRollback()
    {
        $rootDir = Bootstrap::getInstance()->getAppTempDir();
        $backupsDir = __DIR__ . '/_files/var/backups';
        $fileName = 'test.txt';

        $this->filesystem->setRootDir($rootDir)
            ->setBackupsDir($backupsDir)
            ->setTime(1474538269)
            ->setName('code')
            ->setBackupExtension('tgz')
            ->addIgnorePaths([
                $rootDir . '/' . DirectoryList::CONFIG,
                $rootDir . '/' . DirectoryList::VAR_DIR,
                $rootDir . '/' . DirectoryList::PUB,
                $rootDir . '/defaults_extra.cnf',
                $rootDir . '/setup_dump_magento_integration_tests.sql',
            ]);

        $this->assertTrue($this->filesystem->rollback());
        $this->assertTrue(file_exists($rootDir . '/' . $fileName));
    }
}
