<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Indexer;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var bool
     */
    protected static $dbRestored = false;

    /**
     * @inheritDoc
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public static function tearDownAfterClass(): void
    {
        if (empty(static::$dbRestored)) {
            self::restoreFromDb();
        }
    }

    /**
     * Restore DB data after test execution.
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected static function restoreFromDb(): void
    {
        $db = \Magento\TestFramework\Helper\Bootstrap::getInstance()->getBootstrap()
            ->getApplication()
            ->getDbInstance();
        if (!$db->isDbDumpExists()) {
            throw new \LogicException('DB dump does not exist.');
        }
        $db->restoreFromDbDump();
    }
}
