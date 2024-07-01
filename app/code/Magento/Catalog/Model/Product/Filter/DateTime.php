<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Filter;

use Laminas\Filter\FilterInterface;
use Magento\Framework\Stdlib\DateTime as StdlibDateTime;
use Magento\Framework\Stdlib\DateTime\Filter\DateTime as StdlibDateTimeFilter;

/**
 * Product datetime fields values filter
 */
class DateTime implements FilterInterface
{
    /**
     * @var StdlibDateTimeFilter
     */
    private $stdlibDateTimeFilter;

    /**
     * Initializes dependencies.
     *
     * @param StdlibDateTimeFilter $stdlibDateTimeFilter
     */
    public function __construct(StdlibDateTimeFilter $stdlibDateTimeFilter)
    {
        $this->stdlibDateTimeFilter = $stdlibDateTimeFilter;
    }

    /**
     * Convert datetime from locale format to internal format;
     *
     * Make an additional check for MySql date format which is wrongly parsed by IntlDateFormatter
     *
     * @param mixed $value
     * @return mixed|string
     * @throws \Exception
     */
    public function filter($value)
    {
        if (is_string($value)) {
            $value = $this->createDateFromMySqlFormat($value) ?? $value;
        }
        return $this->stdlibDateTimeFilter->filter($value);
    }

    /**
     * Parse a string in MySql date format into a new DateTime object
     *
     * @param string $value
     * @return \DateTime|null
     */
    private function createDateFromMySqlFormat(string $value): ?\DateTime
    {
        $datetime = date_create_from_format(StdlibDateTime::DATETIME_PHP_FORMAT, $value);
        if ($datetime === false) {
            $datetime = date_create_from_format(StdlibDateTime::DATE_PHP_FORMAT, $value);
            if ($datetime !== false) {
                $datetime->setTime(0, 0, 0, 0);
            }
        }
        return $datetime instanceof \DateTime ? $datetime : null;
    }
}
