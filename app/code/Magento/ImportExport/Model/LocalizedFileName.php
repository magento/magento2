<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Model;

use DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Localized filename model
 */
class LocalizedFileName
{
    /**
     * date format regex map
     */
    private const DATE_FORMAT_TO_REGEX = [
        'Y' => '\d{4}',
        'y' => '\d{2}',
        'm' => '\d{2}',
        'n' => '\d{1,2}',
        'M' => '[A-Z][a-z]{3}',
        'F' => '[A-Z][a-z]{2,8}',
        'd' => '\d{2}',
        'j' => '\d{1,2}',
        'D' => '[A-Z][a-z]{2}',
        'l' => '[A-Z][a-z]{6,9}',
        'u' => '\d{1,6}',
        'h' => '\d{2}',
        'H' => '\d{2}',
        'g' => '\d{1,2}',
        'G' => '\d{1,2}',
        'i' => '\d{2}',
        's' => '\d{2}'
    ];

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var array
     */
    private $dateFormats;

    /**
     * @param TimezoneInterface $timezone
     * @param array $dateFormats
     */
    public function __construct(
        TimezoneInterface $timezone,
        array $dateFormats = []
    ) {
        $this->timezone = $timezone;
        $this->dateFormats = $dateFormats;
    }

    /**
     * Get file display name
     *
     * @param string $filename
     * @return string
     */
    public function getFileDisplayName(string $filename): string
    {
        $displayName = $filename;
        foreach ($this->dateFormats as $dateFormat) {
            $utcDate = $this->parseDateFromFilename($filename, $dateFormat);
            if ($utcDate) {
                $utcDateString = $utcDate->format($dateFormat);
                $scopeDate = $this->timezone->scopeDate(null, $utcDate, true);
                $displayName = str_replace($utcDateString, $scopeDate->format($dateFormat), $filename);
                break;
            }
        }
        return $displayName;
    }

    /**
     * Parse date from filename
     *
     * @param string $filename
     * @param string $dateFormat
     * @return DateTime|null
     */
    private function parseDateFromFilename(string $filename, string $dateFormat): ?DateTime
    {
        $date = null;
        $regex = $this->convertDateFormatToRegex($dateFormat);
        if (preg_match("/$regex/", $filename, $result)) {
            $date = date_create_from_format($dateFormat, $result[0]);
            if (!$date || $date->format($dateFormat) !== $result[0]) {
                $date = null;
            }
        }
        return $date;
    }

    /**
     * Convert date format to regex format
     *
     * @param string $dateFormat
     * @return string
     */
    private function convertDateFormatToRegex(string $dateFormat): string
    {
        $regex = '';
        $escape = '\\';
        $chars = str_split($dateFormat);
        foreach ($chars as $pos => $char) {
            $lastChar = $chars[$pos - 1] ?? '';
            if ($lastChar !== $escape && isset(self::DATE_FORMAT_TO_REGEX[$char])) {
                $regex .= self::DATE_FORMAT_TO_REGEX[$char];
            } elseif ($char === $escape) {
                $regex .= $char;
            } else {
                $regex .= preg_quote($char);
            }
        }
        return $regex;
    }
}
