<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Fixture;

use Magento\Framework\Exception\LocalizedException;

/**
 * Data fixture storage service manager
 */
class DataFixtureStorageManager
{
    /**
     * @var DataFixtureStorage
     */
    private static $storage;

    /**
     * Get the unique instance of the storage
     *
     * @return DataFixtureStorage
     * @throws LocalizedException
     */
    public static function getStorage(): DataFixtureStorage
    {
        if (self::$storage === null) {
            throw new LocalizedException(__('Data fixture result storage is not initialized.'));
        }

        return self::$storage;
    }

    /**
     * Set the unique instance of the storage
     *
     * @param DataFixtureStorage $storage
     */
    public static function setStorage(DataFixtureStorage $storage): void
    {
        self::$storage = $storage;
    }
}
