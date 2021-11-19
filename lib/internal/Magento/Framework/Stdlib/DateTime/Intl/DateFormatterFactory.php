<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Stdlib\DateTime\Intl;

use IntlDateFormatter;

/**
 * Class to get Intl date formatter by locale
 */
class DateFormatterFactory
{
    /**
     * Custom date formats by locale
     */
    private const CUSTOM_DATE_FORMATS = [
        'ar_SA' => [
            IntlDateFormatter::SHORT => 'd/MM/y',
        ]
    ];

    /**
     * Create Intl Date formatter
     *
     * The Intl Date formatter gives date formats by ICU standard.
     * http://userguide.icu-project.org/formatparse/datetime
     *
     * @param string $locale
     * @param int $dateStyle
     * @param int $timeStyle
     * @param string|null $timeZone
     * @param bool $useFourDigitsForYear
     * @param bool $useLeadingZeroForMonth
     * @return IntlDateFormatter
     */
    public function create(
        string $locale,
        int $dateStyle,
        int $timeStyle,
        ?string $timeZone = null,
        bool $useFourDigitsForYear = true,
        bool $useLeadingZeroForMonth = true
    ): IntlDateFormatter {
        $formatter = new IntlDateFormatter(
            $locale,
            $dateStyle,
            $timeStyle,
            $timeZone
        );
        /**
         * Process custom date formats
         */
        $customDateFormat = $this->getCustomDateFormat($locale, $dateStyle, $timeStyle);
        if ($customDateFormat !== null) {
            $formatter->setPattern($customDateFormat);
        } elseif ($dateStyle === IntlDateFormatter::SHORT) {
            if ($useFourDigitsForYear) {
                /**
                 * Gives 4 places for year value in short style
                 */
                $longYearPattern = $this->setFourYearPlaces($formatter->getPattern());
                $formatter->setPattern($longYearPattern);
            }

            if ($useLeadingZeroForMonth) {
                $formatter->setPattern($this->setLeadingZeroMonthPlace($formatter->getPattern()));
            }
        }

        return $formatter;
    }

    /**
     * Get custom date format if it exists
     *
     * @param string $locale
     * @param int $dateStyle
     * @param int $timeStyle
     * @return string
     */
    private function getCustomDateFormat(string $locale, int $dateStyle, int $timeStyle): ?string
    {
        $customDateFormat = null;
        if ($dateStyle !== IntlDateFormatter::NONE && isset(self::CUSTOM_DATE_FORMATS[$locale][$dateStyle])) {
            $customDateFormat = self::CUSTOM_DATE_FORMATS[$locale][$dateStyle];
            if ($timeStyle !== IntlDateFormatter::NONE) {
                $timeFormat = (new IntlDateFormatter($locale, IntlDateFormatter::NONE, $timeStyle))
                    ->getPattern();
                $customDateFormat .= ' ' . $timeFormat;
            }
        }

        return $customDateFormat;
    }

    /**
     * Set 4 places for year value in format string
     *
     * @param string $format
     * @return string
     */
    private function setFourYearPlaces(string $format): string
    {
        return preg_replace(
            '/(?<!y)yy(?!y)/',
            'y',
            $format
        );
    }

    /**
     * Set leading zero for month in format string
     *
     * @param string $format
     * @return string
     */
    private function setLeadingZeroMonthPlace(string $format): string
    {
        return preg_replace(
            '/(?<!M)M(?!M)/',
            'MM',
            $format
        );
    }
}
