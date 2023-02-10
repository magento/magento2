<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\DB\Sequence;

/**
 * Interface represents sequence
 *
 * @api
 */
interface SequenceInterface
{
    /**
     * Retrieve current value
     *
     * @return string
     */
    public function getCurrentValue();

    /**
     * Retrieve next value
     *
     * @return string
     */
    public function getNextValue();
}
