<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Sequence;

/**
 * Interface represents sequence
 * @since 2.0.0
 */
interface SequenceInterface
{
    /**
     * Retrieve current value
     *
     * @return string
     * @since 2.0.0
     */
    public function getCurrentValue();

    /**
     * Retrieve next value
     *
     * @return string
     * @since 2.0.0
     */
    public function getNextValue();
}
