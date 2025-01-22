<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Declaration\Schema\Diff;

use Magento\Framework\Setup\Declaration\Schema\Dto\ElementInterface;

/**
 * DiffInterface is type of classes, that holds all information
 * that need to be changed from one installation to another.
 *
 * @api
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
     * @param ElementInterface|object $dtoObject
     * @param string $operation
     * @param ElementInterface $oldDtoObject
     * @return void
     */
    public function register(
        ElementInterface $dtoObject,
        $operation,
        ?ElementInterface $oldDtoObject = null
    );
}
