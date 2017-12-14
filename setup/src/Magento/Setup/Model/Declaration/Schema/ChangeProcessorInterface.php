<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema;

/**
 * Interface ChangeProcessorInterface
 * @package Magento\Setup\Model\Declaration\Schema
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
