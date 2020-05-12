<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Backup;

use Magento\Backup\Helper\Data;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Module\Setup;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Provide tests for \Magento\Framework\Backup\Db.
 */
class DbTest extends \Magento\TestFramework\Indexer\TestCase
{
    public static function setUpBeforeClass(): void
    {
        $db = Bootstrap::getInstance()->getBootstrap()
            ->getApplication()
            ->getDbInstance();
        if (!$db->isDbDumpExists()) {
            throw new \LogicException('DB dump does not exist.');
        }
        $db->restoreFromDbDump();

        parent::setUpBeforeClass();
    }

    /**
     * Test db backup includes triggers.
     *
     * @magentoConfigFixture default/system/backup/functionality_enabled 1
     * @magentoDataFixture Magento/Framework/Backup/_files/trigger.php
     * @magentoDbIsolation disabled
     */
    public function testBackupIncludesCustomTriggers()
    {
        $helper = Bootstrap::getObjectManager()->get(Data::class);
        $time = time();
        $backupManager = Bootstrap::getObjectManager()->get(Factory::class)->create(
            Factory::TYPE_DB
        )->setBackupExtension(
            $helper->getExtensionByType(Factory::TYPE_DB)
        )->setTime(
            $time
        )->setBackupsDir(
            $helper->getBackupsDir()
        )->setName('test_backup');
        $backupManager->create();
        $write = Bootstrap::getObjectManager()->get(Filesystem::class)->getDirectoryWrite(DirectoryList::VAR_DIR);
        $content = $write->readFile('/backups/' . $time . '_db_testbackup.sql');
        $tableName = Bootstrap::getObjectManager()->get(Setup::class)
            ->getTable('test_table_with_custom_trigger');
        $this->assertMatchesRegularExpression(
            '/CREATE  TRIGGER test_custom_trigger AFTER INSERT ON '. $tableName . ' FOR EACH ROW/',
            $content
        );
        //Clean up.
        $write->delete('/backups/' . $time . '_db_testbackup.sql');
    }

    /**
     * teardown
     */
    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
