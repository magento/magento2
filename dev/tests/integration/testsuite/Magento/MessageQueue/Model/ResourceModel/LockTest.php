<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\MessageQueue\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\MessageQueue\LockInterface;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Covers Lock resource model test cases
 */
class LockTest extends TestCase
{
    public function testSaveLock()
    {
        $objectManager = ObjectManager::getInstance();
        /** @var Lock $resourceModel */
        $resourceModel = $objectManager->get(Lock::class);
        $lock = $objectManager->create(LockInterface::class);
        $resourceModel->saveLock($lock);
        self::assertNotEquals(null, $lock->getId());
    }

    /**
     * Only locks older than the configured interval may be released; a lock created just now must survive.
     */
    public function testReleaseOutdatedLocksKeepsRecentLocks()
    {
        $objectManager = ObjectManager::getInstance();
        $connection = $objectManager->get(ResourceConnection::class)->getConnection();
        /** @var Lock $resourceModel */
        $resourceModel = $objectManager->create(Lock::class, ['interval' => 3600]);
        $table = $resourceModel->getTable('queue_lock');

        $recentLock = $objectManager->create(LockInterface::class);
        $recentLock->setMessageCode('lock-test-recent-' . uniqid());
        $resourceModel->saveLock($recentLock);

        $outdatedCode = 'lock-test-outdated-' . uniqid();
        $connection->insert(
            $table,
            ['message_code' => $outdatedCode, 'created_at' => gmdate('Y-m-d H:i:s', time() - 7200)]
        );

        $resourceModel->releaseOutdatedLocks();

        $select = $connection->select()->from($table, 'message_code')->where('message_code LIKE ?', 'lock-test-%');
        $remaining = $connection->fetchCol($select);
        self::assertContains($recentLock->getMessageCode(), $remaining);
        self::assertNotContains($outdatedCode, $remaining);

        $connection->delete($table, ['message_code LIKE ?' => 'lock-test-%']);
    }
}
