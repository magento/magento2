<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MessageQueue\Model\ResourceModel;

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
}
