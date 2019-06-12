<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Stdlib\DateTime;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ScopeInterface;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Phrase;

/**
 * Timezone library
<<<<<<< HEAD
=======
 *
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Timezone implements TimezoneInterface
{
    /**
     * @var array
     */
    protected $_allowedFormats = [
        \IntlDateFormatter::FULL,
        \IntlDateFormatter::LONG,
        \IntlDateFormatter::MEDIUM,
        \IntlDateFormatter::SHORT,
    ];

    /**
     * @var string
     */
    protected $_scopeType;

    /**
     * @var ScopeResolverInterface
     */
    protected $_scopeResolver;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $_dateTime;

    /**
     * @var string
     */
    protected $_defaultTimezonePath;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @param ScopeResolverInterface $scopeResolver
     * @param ResolverInterface $localeResolver
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param ScopeConfigInterface $scopeConfig
     * @param string $scopeType
     * @param string $defaultTimezonePath
     */
    public function __construct(
        ScopeResolverInterface $scopeResolver,
        ResolverInterface $localeResolver,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        ScopeConfigInterface $scopeConfig,
        $scopeType,
        $defaultTimezonePath
    ) {
        $this->_scopeResolver = $scopeResolver;
        $this->_localeResolver = $localeResolver;
        $this->_dateTime = $dateTime;
        $this->_defaultTimezonePath = $defaultTimezonePath;
        $this->_scopeConfig = $scopeConfig;
        $this->_scopeType = $scopeType;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultTimezonePath()
    {
        return $this->_defaultTimezonePath;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultTimezone()
    {
        return 'UTC';
    }

    /**
     * @inheritdoc
     */
    public function getConfigTimezone($scopeType = null, $scopeCode = null)
    {
        return $this->_scopeConfig->getValue(
            $this->getDefaultTimezonePath(),
            $scopeType ?: $this->_scopeType,
            $scopeCode
        );
    }

    /**
     * @inheritdoc
     */
    public function getDateFormat($type = \IntlDateFormatter::SHORT)
    {
        $pattern = (new \IntlDateFormatter(
            $this->_localeResolver->getLocale(),
            $type,
            \IntlDateFormatter::NONE
        ))->getPattern();

        /**
         * This replacement is a workaround to prevent bugs in some third party libraries,
         * that works incorrectly with 'yyyy' value.
         * According to official doc of the ICU library
         * internally used in \Intl, 'yyyy' and 'y' formats are the same
         * @see http://userguide.icu-project.org/formatparse/datetime
         */
        $pattern = str_replace('yyyy', 'y', $pattern);
        return $pattern;
    }

    /**
     * @inheritdoc
     */
    public function getDateFormatWithLongYear()
    {
        return preg_replace(
            '/(?<!y)yy(?!y)/',
            'Y',
            $this->getDateFormat()
        );
    }

    /**
     * @inheritdoc
     */
    public function getTimeFormat($type = \IntlDateFormatter::SHORT)
    {
        return (new \IntlDateFormatter(
            $this->_localeResolver->getLocale(),
            \IntlDateFormatter::NONE,
            $type
        ))->getPattern();
    }

    /**
     * @inheritdoc
     */
    public function getDateTimeFormat($type)
    {
        return $this->getDateFormat($type) . ' ' . $this->getTimeFormat($type);
    }

    /**
     * @inheritdoc
     */
    public function date($date = null, $locale = null, $useTimezone = true, $includeTime = true)
    {
        $locale = $locale ?: $this->_localeResolver->getLocale();
        $timezone = $useTimezone
            ? $this->getConfigTimezone()
            : date_default_timezone_get();

<<<<<<< HEAD
        if (empty($date)) {
            return new \DateTime('now', new \DateTimeZone($timezone));
        } elseif ($date instanceof \DateTime) {
            return $date->setTimezone(new \DateTimeZone($timezone));
        } elseif ($date instanceof \DateTimeImmutable) {
            return new \DateTime($date->format('Y-m-d H:i:s'), $date->getTimezone());
        } elseif (!is_numeric($date)) {
            $date = $this->prepareDate($date, $locale, $timezone, $includeTime);
=======
        switch (true) {
            case (empty($date)):
                return new \DateTime('now', new \DateTimeZone($timezone));
            case ($date instanceof \DateTime):
                return $date->setTimezone(new \DateTimeZone($timezone));
            case ($date instanceof \DateTimeImmutable):
                return new \DateTime($date->format('Y-m-d H:i:s'), $date->getTimezone());
            case (!is_numeric($date)):
                $timeType = $includeTime ? \IntlDateFormatter::SHORT : \IntlDateFormatter::NONE;
                $formatter = new \IntlDateFormatter(
                    $locale,
                    \IntlDateFormatter::SHORT,
                    $timeType,
                    new \DateTimeZone($timezone)
                );

                $date = $this->appendTimeIfNeeded($date, $includeTime);
                $date = $formatter->parse($date) ?: (new \DateTime($date))->getTimestamp();
                break;
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        }

        return (new \DateTime(null, new \DateTimeZone($timezone)))->setTimestamp($date);
    }

    /**
<<<<<<< HEAD
     * Convert string date according to locale format
     *
     * @param string $date
     * @param string $locale
     * @param string $timezone
     * @param bool $includeTime
     * @return string
     */
    private function prepareDate(string $date, string $locale, string $timezone, bool $includeTime) : string
    {
        $timeType = $includeTime ? \IntlDateFormatter::SHORT : \IntlDateFormatter::NONE;
        $formatter = new \IntlDateFormatter(
            $locale,
            \IntlDateFormatter::SHORT,
            $timeType,
            new \DateTimeZone($timezone)
        );

        /**
         * IntlDateFormatter does not parse correctly date formats per some locales
         * It depends on ICU lib version used by intl extension
         * For locales like fr_FR, ar_KW parse date with hyphen as separator
         */
        if ($includeTime) {
            $date = $this->appendTimeIfNeeded($date);
        }
        try {
            $date = $formatter->parse($date) ?: (new \DateTime($date))->getTimestamp();
        } catch (\Exception $e) {
            $date = str_replace('/', '-', $date);
            $date = $formatter->parse($date) ?: (new \DateTime($date))->getTimestamp();
        }

        return $date;
    }

    /**
     * {@inheritdoc}
=======
     * @inheritdoc
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    public function scopeDate($scope = null, $date = null, $includeTime = false)
    {
        $timezone = $this->_scopeConfig->getValue($this->getDefaultTimezonePath(), $this->_scopeType, $scope);
        $date = new \DateTime(is_numeric($date) ? '@' . $date : $date);
        $date->setTimezone(new \DateTimeZone($timezone));
        if (!$includeTime) {
            $date->setTime(0, 0, 0);
        }
        return $date;
    }

    /**
     * @inheritdoc
     */
    public function formatDate($date = null, $format = \IntlDateFormatter::SHORT, $showTime = false)
    {
        $formatTime = $showTime ? $format : \IntlDateFormatter::NONE;

        if (!($date instanceof \DateTimeInterface)) {
            $date = new \DateTime($date);
        }

        return $this->formatDateTime($date, $format, $formatTime);
    }

    /**
     * @inheritdoc
     */
    public function scopeTimeStamp($scope = null)
    {
        $timezone = $this->_scopeConfig->getValue($this->getDefaultTimezonePath(), $this->_scopeType, $scope);
        $currentTimezone = @date_default_timezone_get();
        @date_default_timezone_set($timezone);
        $date = date('Y-m-d H:i:s');
        @date_default_timezone_set($currentTimezone);
        return strtotime($date);
    }

    /**
     * @inheritdoc
     */
    public function isScopeDateInInterval($scope, $dateFrom = null, $dateTo = null)
    {
        if (!$scope instanceof ScopeInterface) {
            $scope = $this->_scopeResolver->getScope($scope);
        }

        $scopeTimeStamp = $this->scopeTimeStamp($scope);
        $fromTimeStamp = strtotime($dateFrom);
        $toTimeStamp = strtotime($dateTo);
        if ($dateTo) {
            // fix date YYYY-MM-DD 00:00:00 to YYYY-MM-DD 23:59:59
            $toTimeStamp += 86400;
        }

<<<<<<< HEAD
        $result = !(!$this->_dateTime->isEmptyDate($dateFrom) && $scopeTimeStamp < $fromTimeStamp)
            && !(!$this->_dateTime->isEmptyDate($dateTo) && $scopeTimeStamp > $toTimeStamp);
        return $result;
    }

    /**
     * @param string|\DateTimeInterface $date
     * @param int $dateType
     * @param int $timeType
     * @param string|null $locale
     * @param string|null $timezone
     * @param string|null $pattern
     * @return string
=======
        return !(!$this->_dateTime->isEmptyDate($dateFrom) && $scopeTimeStamp < $fromTimeStamp ||
               !$this->_dateTime->isEmptyDate($dateTo) && $scopeTimeStamp > $toTimeStamp);
    }

    /**
     * @inheritdoc
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    public function formatDateTime(
        $date,
        $dateType = \IntlDateFormatter::SHORT,
        $timeType = \IntlDateFormatter::SHORT,
        $locale = null,
        $timezone = null,
        $pattern = null
    ) {
        if (!($date instanceof \DateTimeInterface)) {
            $date = new \DateTime($date);
        }

        if ($timezone === null) {
            if ($date->getTimezone() == null || $date->getTimezone()->getName() == 'UTC'
                || $date->getTimezone()->getName() == '+00:00'
            ) {
                $timezone = $this->getConfigTimezone();
            } else {
                $timezone = $date->getTimezone();
            }
        }

        $formatter = new \IntlDateFormatter(
            $locale ?: $this->_localeResolver->getLocale(),
            $dateType,
            $timeType,
            $timezone,
            null,
            $pattern
        );
        return $formatter->format($date);
    }

    /**
     * @inheritdoc
     */
    public function convertConfigTimeToUtc($date, $format = 'Y-m-d H:i:s')
    {
        if (!($date instanceof \DateTimeInterface)) {
            if ($date instanceof \DateTimeImmutable) {
                $date = new \DateTime($date->format('Y-m-d H:i:s'), new \DateTimeZone($this->getConfigTimezone()));
            } else {
                $date = new \DateTime($date, new \DateTimeZone($this->getConfigTimezone()));
            }
        } else {
            if ($date->getTimezone()->getName() !== $this->getConfigTimezone()) {
                throw new LocalizedException(
                    new Phrase(
                        'The DateTime object timezone needs to be the same as the "%1" timezone in config.',
                        $this->getConfigTimezone()
                    )
                );
            }
        }

        $date->setTimezone(new \DateTimeZone('UTC'));

        return $date->format($format);
    }

    /**
<<<<<<< HEAD
     * Add time in case if no time provided but required
     *
     * @param string $date
     * @return string
     */
    private function appendTimeIfNeeded(string $date) : string
    {
        if (!preg_match('/\d{1,2}:\d{2}/', $date)) {
            $date .= " 00:00";
=======
     * Retrieve date with time
     *
     * @param string $date
     * @param bool $includeTime
     * @return string
     */
    private function appendTimeIfNeeded($date, $includeTime)
    {
        if ($includeTime && !preg_match('/\d{1}:\d{2}/', $date)) {
            $date .= " 0:00am";
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        }
        return $date;
    }
}
