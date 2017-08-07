<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Stdlib\DateTime\Filter;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Date filter. Converts date from localized to internal format.
 *
 * @api
 */
class Date implements \Zend_Filter_Interface
{
    /**
     * Filter that converts localized input into normalized format
     *
     * @var \Zend_Filter_LocalizedToNormalized
     *
     * @deprecated 2.1.0
     */
    protected $_localToNormalFilter;

    /**
     * Filter that converts normalized input into internal format
     *
     * @var \Zend_Filter_NormalizedToLocalized
     *
     * @deprecated 2.1.0
     */
    protected $_normalToLocalFilter;

    /**
     * @var TimezoneInterface
     *
     */
    protected $_localeDate;

    /**
     * @param TimezoneInterface $localeDate
     *
     */
    public function __construct(TimezoneInterface $localeDate)
    {
        $this->_localeDate = $localeDate;
        $this->_localToNormalFilter = new \Zend_Filter_LocalizedToNormalized(
            ['date_format' => $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT)]
        );
        $this->_normalToLocalFilter = new \Zend_Filter_NormalizedToLocalized(
            ['date_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT]
        );
    }

    /**
     * Convert date from localized to internal format
     *
     * @param string $value
     * @return string
     * @throws \Exception
     */
    public function filter($value)
    {
        try {
            $value = $this->_localeDate->date($value, null, false);
            return $value->format('Y-m-d');
        } catch (\Exception $e) {
            throw new \Exception("Invalid input date format '$value'");
        }
    }
}
