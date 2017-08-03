<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Intl;

/**
 * Class DateTimeFactory
 * @package Magento\Framework
 * @since 2.0.0
 */
class DateTimeFactory
{
    /**
     * Factory method for \DateTime
     *
     * @param string $time
     * @param \DateTimeZone $timezone
     * @return \DateTime
     * @since 2.0.0
     */
    public function create($time = 'now', \DateTimeZone $timezone = null)
    {
        return new \DateTime($time, $timezone);
    }
}
