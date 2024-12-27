<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Stdlib\DateTime\Filter;

use Exception;
use IntlDateFormatter;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filter\LocalizedToNormalized;
use Magento\Framework\Filter\NormalizedToLocalized;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Date/Time filter. Converts datetime from localized to internal format.
 *
 * @api
 * @since 100.0.2
 */
class DateTime extends Date
{
    /**
     * @param TimezoneInterface $localeDate
     */
    public function __construct(TimezoneInterface $localeDate)
    {
        parent::__construct($localeDate);
        $this->_localToNormalFilter = new LocalizedToNormalized(
            [
                'date_format' => $this->_localeDate->getDateTimeFormat(
                    IntlDateFormatter::SHORT
                ),
            ]
        );
        $this->_normalToLocalFilter = new NormalizedToLocalized(
            ['date_format' => \Magento\Framework\Stdlib\DateTime::DATETIME_INTERNAL_FORMAT]
        );
    }

    /**
     * Convert date from localized to internal format
     *
     * @param string $value
     * @return string
     * @throws LocalizedException
     * @since 100.1.0
     */
    public function filter($value)
    {
        try {
            $dateTime = $this->_localeDate->date($value, null, false);
            return $dateTime->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            throw new LocalizedException(
                __('Invalid input datetime format of value "%1"', $value)
            );
        }
    }
}
