<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Form Input/Output Strip HTML tags Filter
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Data\Form\Filter;

use Magento\Framework\Stdlib\DateTime;

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
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $localeResolver;

    /**
     * Initialize filter
     *
     * @param string $format \DateTime input/output format
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     */
    public function __construct(
        $format = null,
        \Magento\Framework\Locale\ResolverInterface $localeResolver = null
    ) {
        if ($format === null) {
            $format = DateTime::DATE_INTERNAL_FORMAT;
        }
        $this->_dateFormat = $format;
        $this->localeResolver = $localeResolver;
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
            ['date_format' => $this->_dateFormat, 'locale' => $this->localeResolver->getLocale()]
        );
        $filterInternal = new \Zend_Filter_NormalizedToLocalized(
            ['date_format' => DateTime::DATE_INTERNAL_FORMAT, 'locale' => $this->localeResolver->getLocale()]
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
            ['date_format' => DateTime::DATE_INTERNAL_FORMAT, 'locale' => $this->localeResolver->getLocale()]
        );
        $filterInternal = new \Zend_Filter_NormalizedToLocalized(
            ['date_format' => $this->_dateFormat, 'locale' => $this->localeResolver->getLocale()]
        );

        $value = $filterInput->filter($value);
        $value = $filterInternal->filter($value);
        return $value;
    }
}
