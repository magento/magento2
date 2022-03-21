<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Fixture;

use Magento\Framework\DataObject;

/**
 * Data fixture storage model
 */
class DataFixtureStorage
{
    /**
     * @var array
     */
    private $fixtures = [];

    /**
     * Get fixture result by its identifier
     *
     * @param string $name
     * @return DataObject|null
     */
    public function get(string $name): ?DataObject
    {
        return $this->fixtures[$name] ?? null;
    }

    /**
     * Persist fixture result to the storage
     *
     * @param string $name
     * @param DataObject|null $data
     */
    public function persist(string $name, ?DataObject $data): void
    {
        $this->fixtures[$name] = $data;
    }

    /**
     * Flush the storage
     */
    public function flush(): void
    {
        $this->fixtures = [];
    }
}
