<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wonderland\Api\Data;

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
