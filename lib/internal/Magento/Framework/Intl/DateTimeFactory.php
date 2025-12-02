<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
    public function create($time = 'now', ?\DateTimeZone $timezone = null)
    {
        return new \DateTime($time, $timezone);
    }
}
