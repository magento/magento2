<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Diff;

use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\Schema;
use Magento\Setup\Model\Declaration\Schema\ElementHistory;
use Magento\Setup\Model\Declaration\Schema\Request;

/**
 * DiffInterface is type of classes, that holds all information
 * that need to be changed from one installation to another.
 */
interface DiffInterface
{
    /**
     * Retrieve operations by type.
     *
     * Please note: that we wants to save history and we retrieve next structure:
     * [
     *   'column_a' => ElementHistory [
     *      'new' => [
     *          ...
     *      ],
     *      'old' => [
     *          ...
     *      ]
     *   ]
     * ]
     *
     * @return array
     */
    public function getAll();

    /**
     * Register operation.
     *
     * @param  ElementInterface|object $dtoObject
     * @param  string                  $operation
     * @param  ElementInterface        $oldDtoObject
     * @return void
     */
    public function register(
        ElementInterface $dtoObject,
        $operation,
        ElementInterface $oldDtoObject = null
    );
}
