<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Formatter;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Change datetime as per timezone
 */
class Converter
{
    /**
     * @var TimezoneInterface
     */
    private TimezoneInterface $timezone;

    /**
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        TimezoneInterface $timezone
    ) {
        $this->timezone = $timezone;
    }

    /**
     * Retrieve the timezone date
     *
     * @param string $date
     * @return string
     */
    public function getFormatDate(string $date): string
    {
        return $this->timezone->date(
            $date
        )->format('Y-m-d H:i:s');
    }
}
