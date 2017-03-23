<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Sequence;

/**
 * Interface represents sequence
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
