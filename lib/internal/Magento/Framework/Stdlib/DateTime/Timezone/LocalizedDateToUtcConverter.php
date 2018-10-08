<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Stdlib\DateTime\Timezone;

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
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var string
     */
    private $scopeType;

    /**
     * @var string
     */
    private $defaultTimezonePath;

    /**
     * LocalizedDateToUtcConverter constructor.
     *
     * @param ResolverInterface $localeResolver
     */
    public function __construct(
        ResolverInterface $localeResolver,
        ScopeConfigInterface $scopeConfig,
        $scopeType,
        $defaultTimezonePath
    )
    {
        $this->localeResolver = $localeResolver;
        $this->scopeConfig = $scopeConfig;
        $this->scopeType = $scopeType;
        $this->defaultTimezonePath = $defaultTimezonePath;
    }

    /**
     * @inheritdoc
     */
    public function convertLocalizedDateToUtc($date)
    {
        $locale = $this->localeResolver->getLocale();
        $formatter = new \IntlDateFormatter(
            $locale,
            \IntlDateFormatter::MEDIUM,
            \IntlDateFormatter::MEDIUM,
            $this->getConfigTimezone(),
            null,
            null
        );
        $unixTime = $formatter->parse($date);
        $dateTime = new DateTime($this);
        $dateUniversal = $dateTime->gmtDate(null, $unixTime);
        $date = new \DateTime($dateUniversal, new \DateTimeZone($this->getConfigTimezone()));

        $date->setTimezone(new \DateTimeZone('UTC'));

        return $date->format($this->defaultFormat);
    }
}