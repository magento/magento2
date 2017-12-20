<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema;

/**
 * With help of this interface you can go through all element types
 * and apply difference, that is persisted in ChangeRegistry
 * For example, if you have 2 columns were registered for removal, they will be catched
 * with specific operation and removed
 */
interface OperationInterface
{
    /**
     * Retrieve operation identifier, by which we can find it
     *
     * @return string
     */
    public function getOperationName();

    /**
     * Apply change of any type
     *
     * @param ElementHistory $elementHistory
     * @return void
     */
    public function doOperation(ElementHistory $elementHistory);
}
