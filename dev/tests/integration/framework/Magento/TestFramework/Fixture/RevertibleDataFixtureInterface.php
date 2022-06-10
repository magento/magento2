<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Fixture;

use Magento\Framework\DataObject;

/**
 * Interface for revertible data fixtures
 */
interface RevertibleDataFixtureInterface extends DataFixtureInterface
{
    /**
     * Revert fixture data
     *
     * @param DataObject $data
     */
    public function revert(DataObject $data): void;
}
