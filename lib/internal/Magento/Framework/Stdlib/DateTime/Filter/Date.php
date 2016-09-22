<?php
/**
 * Date filter. Converts date from localized to internal format.
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Stdlib\DateTime\Filter;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Phrase;

class Date implements \Zend_Filter_Interface
{
    /**
     * Filter that converts localized input into normalized format
     *
     * @var \Zend_Filter_LocalizedToNormalized
     *
     * @deprecated
     */
    protected $_localToNormalFilter;

    /**
     * Filter that converts normalized input into internal format
     *
     * @var \Zend_Filter_NormalizedToLocalized
     *
     * @deprecated
     */
    protected $_normalToLocalFilter;

    /**
     * @var TimezoneInterface
     *
     * @deprecated
     */
    protected $_localeDate;

    /**
     * @param TimezoneInterface $localeDate
     *
     * @deprecated
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
            $value = new \DateTime($value);
            return $value->format('Y-m-d');
        } catch (\Exception $e) {
            throw new \Exception("Invalid input date format '$value'");
        }
    }
}
