<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Isolation;

use Magento\TestFramework\Fixture\DataFixtureStorageManager;

/**
 * Test case hooks observer for data fixture storage
 */
class FlushDataFixtureStorage
{
    /**
     * Flush data fixture storage before each test
     *
     * @param \PHPUnit\Framework\TestCase $test
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function startTest(\PHPUnit\Framework\TestCase $test)
    {
        DataFixtureStorageManager::getStorage()->flush();
    }
}
