<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Mftf\Helper;

use Magento\FunctionalTestingFramework\Helper\Helper;

/**
 * Formats dates for MFTF assertions the same way the admin grid date column does.
 *
 * The cart price rule grid renders dates server-side via
 * \Magento\Backend\Block\Widget\Grid\Column\Renderer\Date, which uses PHP's
 * IntlDateFormatter::MEDIUM. Generating the "expected" value with the browser's
 * own Intl implementation instead compares two independent, independently
 * versioned ICU/CLDR data sets, so locale-specific abbreviations (e.g. Hindi
 * month names) can legitimately differ and fail the assertion even when the
 * application is rendering correctly.
 */
class LocalizedGridDateHelper extends Helper
{
    /**
     * Format an ISO (Y-m-d) date using the same formatter and settings as the admin grid date column.
     *
     * @param string $isoDate
     * @param string $locale
     * @return string
     */
    public function formatGridDate(string $isoDate, string $locale): string
    {
        $date = new \DateTime($isoDate, new \DateTimeZone('UTC'));
        $formatter = new \IntlDateFormatter(
            $locale,
            \IntlDateFormatter::MEDIUM,
            \IntlDateFormatter::NONE,
            'UTC'
        );

        return (string) $formatter->format($date);
    }
}
