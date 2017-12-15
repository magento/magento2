<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema;

/**
 * With help of ChangeProcessorInterface you can go thorugh all element types
 * and apply difference, that is persisted in ChangeRegistry
 */
interface ChangeProcessorInterface
{
    /**
     * Apply change of any type
     *
     * @param ChangeRegistryInterface $changeRegistry
     * @return void
     */
    public function apply(ChangeRegistryInterface $changeRegistry);
}
