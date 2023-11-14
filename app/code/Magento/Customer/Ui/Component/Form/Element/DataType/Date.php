<?php
/************************************************************************
 *
 * Copyright 2023 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ************************************************************************
 */
declare(strict_types=1);

namespace Magento\Customer\Ui\Component\Form\Element\DataType;

use Exception;
use Magento\Ui\Component\Form\Element\DataType\Date as UiComponentDate;
use Magento\Framework\Stdlib\DateTime\Intl\DateFormatterFactory;

/**
 * Format the filtered date in UI grid based on specific locale
 */
class Date
{
    /**
     * @param DateFormatterFactory $dateFormatterFactory
     */
    public function __construct(
        private DateFormatterFactory $dateFormatterFactory
    ) {
    }

    /**
     * Convert given filter date to specific date format based on locale
     *
     * @param UiComponentDate $subject
     * @param string $date
     * @param int $hour
     * @param int $minute
     * @param int $second
     * @param bool $setUtcTimeZone
     * @return array
     * @throws Exception
     */
    public function beforeConvertDate(
        UiComponentDate $subject,
        string $date,
        int $hour,
        int $minute,
        int $second,
        bool $setUtcTimeZone
    ): array {
        $formatter = $this->dateFormatterFactory->create(
            $subject->getLocale(),
            \IntlDateFormatter::SHORT,
            \IntlDateFormatter::NONE,
            date_default_timezone_get()
        );
        $formatter->setLenient(false);
        if (!$formatter->parse($date)) {
            $date = $formatter->formatObject(
                new \DateTime($date),
                $formatter->getPattern()
            );
        }
        return [$date, $hour, $minute, $second, $setUtcTimeZone];
    }
}
