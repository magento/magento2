<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Fixture;

use Magento\Framework\DataObject;

/**
 * Interface for data fixtures
 */
interface DataFixtureInterface
{
    /**
     * Apply fixture data
     *
     * @param array $data
     * @return DataObject|null
     */
    public function apply(array $data = []): ?DataObject;
}
