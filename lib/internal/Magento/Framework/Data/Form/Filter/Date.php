<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Form Input/Output Strip HTML tags Filter
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Data\Form\Filter;

class Date implements \Magento\Framework\Data\Form\Filter\FilterInterface
{
    /**
     * Date format
     *
     * @var string
     */
    protected $_dateFormat;

    /**
     * Local
     *
     * @var \Zend_Locale
     */
    protected $_locale;

    /**
     * Initialize filter
     *
     * @param string $format    \Magento\Framework\Stdlib\DateTime\Date input/output format
     * @param \Zend_Locale $locale
     */
    public function __construct($format = null, $locale = null)
    {
        if (is_null($format)) {
            $format = \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT;
        }
        $this->_dateFormat = $format;
        $this->_locale = $locale;
    }

    /**
     * Returns the result of filtering $value
     *
     * @param string $value
     * @return string
     */
    public function inputFilter($value)
    {
        $filterInput = new \Zend_Filter_LocalizedToNormalized(
            ['date_format' => $this->_dateFormat, 'locale' => $this->_locale]
        );
        $filterInternal = new \Zend_Filter_NormalizedToLocalized(
            ['date_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT, 'locale' => $this->_locale]
        );

        $value = $filterInput->filter($value);
        $value = $filterInternal->filter($value);
        return $value;
    }

    /**
     * Returns the result of filtering $value
     *
     * @param string $value
     * @return string
     */
    public function outputFilter($value)
    {
        $filterInput = new \Zend_Filter_LocalizedToNormalized(
            ['date_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT, 'locale' => $this->_locale]
        );
        $filterInternal = new \Zend_Filter_NormalizedToLocalized(
            ['date_format' => $this->_dateFormat, 'locale' => $this->_locale]
        );

        $value = $filterInput->filter($value);
        $value = $filterInternal->filter($value);
        return $value;
    }
}
