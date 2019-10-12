<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Stdlib\DateTime\Timezone;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class LocalizedDateToUtcConverter
 */
class LocalizedDateToUtcConverter implements LocalizedDateToUtcConverterInterface
{
    /**
     * Contains default date format
     *
     * @var string
     */
    private $defaultFormat = 'Y-m-d H:i:s';

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * LocalizedDateToUtcConverter constructor.
     *
     * @param TimezoneInterface $timezone
     * @param ResolverInterface $localeResolver
     */
    public function __construct(
        TimezoneInterface $timezone,
        ResolverInterface $localeResolver
    ) {
        $this->timezone = $timezone;
        $this->localeResolver = $localeResolver;
    }

    /**
     * @inheritdoc
     */
    public function convertLocalizedDateToUtc($date)
    {
        $configTimezone = $this->timezone->getConfigTimezone();
        $locale = $this->localeResolver->getLocale();

        $formatter = new \IntlDateFormatter(
            $locale,
            \IntlDateFormatter::MEDIUM,
            \IntlDateFormatter::MEDIUM,
            $configTimezone
        );

        $localTimestamp = $formatter->parse($date);
        $gmtTimestamp = $this->timezone->date($localTimestamp)->getTimestamp();
        $formattedUniversalTime = date($this->defaultFormat, $gmtTimestamp);

        $date = new \DateTime($formattedUniversalTime, new \DateTimeZone($configTimezone));
        $date->setTimezone(new \DateTimeZone('UTC'));

        return $date->format($this->defaultFormat);
    }
}
