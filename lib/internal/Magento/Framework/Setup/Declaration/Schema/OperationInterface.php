<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Declaration\Schema;

use Magento\Framework\Setup\Declaration\Schema\Db\Statement;

/**
 * Schema operation interface.
 */
interface OperationInterface
{
    /**
     * Retrieve operation identifier key.
     *
     * @return string
     */
    public function getOperationName();

    /**
     * Is operation destructive flag.
     *
     * Destructive operations can make system unstable.
     *
     * For example, if operation is destructive it can remove table or column created not with
     * declarative schema (for example with old migration script).
     *
     * @return bool
     */
    public function isOperationDestructive();

    /**
     * Apply change of any type.
     *
     * @param  ElementHistory $elementHistory
     * @return Statement[]
     */
    public function doOperation(ElementHistory $elementHistory);
}
