<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestModuleExtensionAttributes\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Fake interface
 * to test exception if the method 'getExtensionAttributes' does not return concrete type
 */
interface FakeExtensibleTwoInterface extends ExtensibleDataInterface
{
    /**
     * test incorrect return type
     *
     * @return int
     */
    public function getExtensionAttributes();
}
