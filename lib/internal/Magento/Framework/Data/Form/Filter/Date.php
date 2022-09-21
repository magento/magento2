<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Data\Form\Filter;

use Exception;
use Magento\Framework\Filter\LocalizedToNormalized;
use Magento\Framework\Filter\NormalizedToLocalized;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime;

/**
 * Form Input/Output Strip HTML tags Filter
 */
class Date implements FilterInterface
{
    /**
     * @var string
     */
    protected $_dateFormat;

    /**
     * @var ResolverInterface
     */
    protected $localeResolver;

    /**
     * Initialize filter
     *
     * @param string|null $format \DateTime input/output format
     * @param ResolverInterface|null $localeResolver
     */
    public function __construct(
        string $format = null,
        ResolverInterface $localeResolver = null
    ) {
        $this->_dateFormat = $format ?? DateTime::DATE_INTERNAL_FORMAT;
        $this->localeResolver = $localeResolver;
    }

    /**
     * Returns the result of filtering $value
     *
     * @param string $value
     * @return string
     * @throws Exception
     */
    public function inputFilter($value)
    {
        if (!$value) {
            return $value;
        }

        $filterInput = new LocalizedToNormalized(
            ['date_format' => $this->_dateFormat, 'locale' => $this->localeResolver->getLocale()]
        );
        $filterInternal = new NormalizedToLocalized(
            ['date_format' => DateTime::DATE_INTERNAL_FORMAT, 'locale' => $this->localeResolver->getLocale()]
        );

        $value = $filterInput->filter($value);
        return $filterInternal->filter($value);
    }

    /**
     * Returns the result of filtering $value
     *
     * @param string $value
     * @return string
     * @throws Exception
     */
    public function outputFilter($value)
    {
        if (!$value) {
            return $value;
        }

        $filterInput = new LocalizedToNormalized(
            ['date_format' => DateTime::DATE_INTERNAL_FORMAT, 'locale' => $this->localeResolver->getLocale()]
        );
        $filterInternal = new NormalizedToLocalized(
            ['date_format' => $this->_dateFormat, 'locale' => $this->localeResolver->getLocale()]
        );

        $value = $filterInput->filter($value);
        return $filterInternal->filter($value);
    }
}
