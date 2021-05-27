<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Fixture;

/**
 * Interface for revertible data fixtures
 */
interface RevertibleDataFixtureInterface extends DataFixtureInterface
{
    /**
     * Revert fixture data
     *
     * @param array $data
     */
    public function revert(array $data = []): void;
}
