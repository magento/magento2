<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Workaround\Override\Fixture\Applier;

/**
 * Interface ApplierInterface must be implemented in applier
 */
interface ApplierInterface
{
    /**
     * Apply configurations to fixtures
     *
     * @param array $fixtures
     * @return array
     */
    public function apply(array $fixtures): array;
}
