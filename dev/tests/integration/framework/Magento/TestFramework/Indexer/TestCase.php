<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
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
