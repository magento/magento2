<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
     * @param DataObject $data
     * @return DataObject
     */
    public function apply(DataObject $data): ?DataObject;
}
