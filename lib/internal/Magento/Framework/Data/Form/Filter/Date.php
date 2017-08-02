<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Form Input/Output Strip HTML tags Filter
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Data\Form\Filter;

use Magento\Framework\Stdlib\DateTime;

/**
 * Class \Magento\Framework\Data\Form\Filter\Date
 *
 * @since 2.0.0
 */
class Date implements \Magento\Framework\Data\Form\Filter\FilterInterface
{
    /**
     * Date format
     *
     * @var string
     * @since 2.0.0
     */
    protected $_dateFormat;

    /**
     * Local
     *
     * @var \Magento\Framework\Locale\ResolverInterface
     * @since 2.0.0
     */
    protected $localeResolver;

    /**
     * Initialize filter
     *
     * @param string $format \DateTime input/output format
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
