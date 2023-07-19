<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Intl;

class DateTimeFactory
{
    /**
     * Factory method for \DateTime
     *
     * @param string $time
     * @param \DateTimeZone $timezone
     * @return \DateTime
     */
    public function create($time = 'now', \DateTimeZone $timezone = null)
    {
        return new \DateTime($time, $timezone);
    }
}
