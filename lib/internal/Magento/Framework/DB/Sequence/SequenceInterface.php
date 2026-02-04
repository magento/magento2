<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
