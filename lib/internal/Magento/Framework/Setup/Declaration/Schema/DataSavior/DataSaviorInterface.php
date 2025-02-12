<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Declaration\Schema\DataSavior;

use Magento\Framework\Setup\Declaration\Schema\Dto\ElementInterface;

/**
 * This interface allows to dump data during declarative installation process
 * and revert changes with applying previously saved data, if something goes wrong
 *
 * @api
 */
interface DataSaviorInterface
{
    /**
     * Generate dump file by element
     *
     * For example, it can generate file for removed column is_allowed, in this case
     * this file will consists of data in is_allowed column and additional data, that allows
     * to identify each `is_allowed` value
     *
     * @param ElementInterface $element
     * @return void
     */
    public function dump(ElementInterface $element);

    /**
     * Find the field, that was backed up by file name, and tries to restore data
     * that is in this file
     *
     * @param ElementInterface $element
     * @return mixed
     */
    public function restore(ElementInterface $element);

    /**
     * Check whether this element is acceptable by current implementation of data savior
     *
     * @param ElementInterface $element
     * @return bool
     */
    public function isAcceptable(ElementInterface $element);
}
