<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Locale;

use Magento\Framework\Locale\ResolverInterface as LocalResolverInterface;

/**
 * Format numbers to a locale
 */
class LocaleFormatter
{
    /**
     * @var LocalResolverInterface
     */
    private $localeResolver;

    /**
     * @var \NumberFormatter
     */
    private $numberFormatter;

    /**
     * @param LocalResolverInterface $localeResolver
     */
    public function __construct(
        LocalResolverInterface $localeResolver
    ) {
        $this->localeResolver = $localeResolver;
    }

    /**
     * Get locale code in JS format
     *
     * @return string
     */
    public function getLocaleJs(): string
    {
        return str_replace("_", "-", $this->localeResolver->getLocale());
    }

    /**
     * Localize given number
     *
     * @param string|float|int|null $number
     * @return false|string
     */
    public function formatNumber($number)
    {
        if (!is_float($number) && !is_int($number)) {
            $number = (int) $number;
        }

        if (!$this->numberFormatter) {
            $this->numberFormatter = numfmt_create($this->localeResolver->getLocale(), \NumberFormatter::TYPE_DEFAULT);
        }
        return $this->numberFormatter->format($number);
    }
}
