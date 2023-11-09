<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Ui\Component\Form\Element\DataType;

use Exception;
use Magento\Ui\Component\Form\Element\DataType\Date as UiComponentDate;
use Magento\Framework\Stdlib\DateTime\Intl\DateFormatterFactory;

/**
 * Format the filtered date in customer grid based on specific locale
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
        $formatter->setLenient(true);
        return [$date, $hour, $minute, $second, $setUtcTimeZone];
    }
}
