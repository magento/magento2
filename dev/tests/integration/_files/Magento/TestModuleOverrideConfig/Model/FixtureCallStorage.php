<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestModuleOverrideConfig\Model;

/**
 * Class represent simple container to save data
 */
class FixtureCallStorage
{
    /** @var array */
    private $storage = [];

    /**
     * Add fixture to storage
     *
     * @param string $fixture
     */
    public function addFixtureToStorage(string $fixture): void
    {
        $this->storage[] = $fixture;
    }

    /**
     * Get fixture position in storage
     *
     * @param string $fixture
     * @return false|int
     */
    public function getFixturePosition(string $fixture)
    {
        return array_search($fixture, $this->storage);
    }

    /**
     * Get storage
     *
     * @return array
     */
    public function getStorage(): array
    {
        return $this->storage;
    }

    /**
     * Get fixtures count in storage
     *
     * @param string $fixture
     * @return int
     */
    public function getFixturesCount(string $fixture = ''): int
    {
        $count = count($this->storage);
        if ($fixture) {
            $result = array_filter($this->storage, function ($storedFixture) use ($fixture) {
                return $storedFixture === $fixture;
            });
            $count = count($result);
        }

        return $count;
    }

    /**
     * Clear storage
     *
     * @return void
     */
    public function clearStorage(): void
    {
        $this->storage = [];
    }
}
