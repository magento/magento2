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
     * @param string $identifier
     * @return DataObject|null
     */
    public function get(string $identifier): ?DataObject
    {
        return $this->fixtures[$identifier] ?? null;
    }

    /**
     * Persist fixture result to the storage
     *
     * @param string $identifier
     * @param DataObject|null $data
     */
    public function persist(string $identifier, ?DataObject $data): void
    {
        $this->fixtures[$identifier] = $data;
    }

    /**
     * Flush the storage
     */
    public function flush(): void
    {
        $this->fixtures = [];
    }
}
