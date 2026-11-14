<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Backup\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @see Backup
 * @magentoAppArea adminhtml
 */
class BackupTest extends TestCase
{
    private const BACKUP_TIME = 1700000000;
    private const BACKUP_PATH = 'backups';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var WriteInterface
     */
    private $varDirectory;

    /**
     * @var string
     */
    private $backupFile;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->varDirectory = $this->objectManager->get(Filesystem::class)
            ->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->backupFile = self::BACKUP_PATH . '/' . self::BACKUP_TIME . '_db_integration.sql';
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        if ($this->varDirectory->isExist($this->backupFile)) {
            $this->varDirectory->delete($this->backupFile);
        }
    }

    /**
     * The contents of the backup file are returned, not a listing of the path.
     */
    public function testGetFileReturnsBackupContents(): void
    {
        $contents = '-- integration backup contents';
        $this->varDirectory->writeFile($this->backupFile, $contents);

        $this->assertSame($contents, $this->createBackup()->getFile());
    }

    /**
     * A missing backup file is reported as a localized exception.
     */
    public function testGetFileThrowsExceptionWhenBackupIsMissing(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('The backup file does not exist.');

        $this->createBackup()->getFile();
    }

    /**
     * Create a backup model pointing at the fixture file.
     *
     * @return Backup
     */
    private function createBackup(): Backup
    {
        $backup = $this->objectManager->create(Backup::class);
        $backup->setPath(self::BACKUP_PATH)
            ->setName('integration')
            ->setTime(self::BACKUP_TIME);
        $backup->setType('db');

        return $backup;
    }
}
