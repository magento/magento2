<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Stdlib\DateTime\Filter;

use Exception;
use Laminas\Filter\FilterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filter\LocalizedToNormalized;
use Magento\Framework\Filter\NormalizedToLocalized;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Date filter. Converts date from localized to internal format.
 *
 * @api
 * @since 100.0.2
 */
class Date implements FilterInterface
{
    /**
     * Filter that converts localized input into normalized format
     *
     * @var LocalizedToNormalized
     *
     * @deprecated 100.1.0
     * @see no alternatives
     */
    protected $_localToNormalFilter;

    /**
     * Filter that converts normalized input into internal format
     *
     * @var NormalizedToLocalized
     *
     * @deprecated 100.1.0
     * @see no alternatives
     */
    protected $_normalToLocalFilter;

    /**
     * @var TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @param TimezoneInterface $localeDate
     */
    public function __construct(TimezoneInterface $localeDate)
    {
        $this->_localeDate = $localeDate;
        $this->_localToNormalFilter = new LocalizedToNormalized(
            ['date_format' => $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT)]
        );
        $this->_normalToLocalFilter = new NormalizedToLocalized(
            ['date_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT]
        );
    }

    /**
     * Convert date from localized to internal format
     *
     * @param string $value
     * @return string
     * @throws LocalizedException
     */
    public function filter($value)
    {
        try {
            $value = $this->_localeDate->date($value, null, false, false);
            return $value->format('Y-m-d');
        } catch (Exception $e) {
            throw new LocalizedException(
                __('Invalid input date format "%1"', $value)
            );
        }
    }
}
