<?php
/**
 * Date filter. Converts date from localized to internal format.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Stdlib\DateTime\Filter;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Date implements \Zend_Filter_Interface
{
    /**
     * Filter that converts localized input into normalized format
     *
     * @var \Zend_Filter_LocalizedToNormalized
     */
    protected $_localToNormalFilter;

    /**
     * Filter that converts normalized input into internal format
     *
     * @var \Zend_Filter_NormalizedToLocalized
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
        $this->_localToNormalFilter = new \Zend_Filter_LocalizedToNormalized(
            ['date_format' => $this->_localeDate->getDateFormat(TimezoneInterface::FORMAT_TYPE_SHORT)]
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
     */
    public function filter($value)
    {
        return $this->_normalToLocalFilter->filter($this->_localToNormalFilter->filter($value));
    }
}
