<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Stdlib\DateTime;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Phrase;

/**
 * {@inheritdoc}
 */
class DateTimeFormatter implements DateTimeFormatterInterface
{
    /**
     * @var bool
     */
    protected $useIntlFormatObject;

    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @param bool|null $useIntlFormatObject
     * @param ResolverInterface|null $localeResolver
     */
    public function __construct(
        $useIntlFormatObject = null,
        ?ResolverInterface $localeResolver = null
    ) {
        $this->useIntlFormatObject = $useIntlFormatObject ?? !\defined('HHVM_VERSION');
        $this->localeResolver = $localeResolver
            ?? ObjectManager::getInstance()->get(ResolverInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function formatObject($object, $format = null, $locale = null)
    {
        $locale = $locale ?? $this->localeResolver->getLocale();
        if ($this->useIntlFormatObject) {
            return \IntlDateFormatter::formatObject($object, $format, $locale);
        }
        return $this->doFormatObject($object, $format, $locale);
    }

    /**
     * Implements what IntlDateFormatter::formatObject() is in PHP 5.5+
     *
     * @param \IntlCalendar|\DateTimeInterface $object
     * @param string|int|array|null $format
     * @param string|null $locale
     * @return string
     * @throws LocalizedException
     */
    protected function doFormatObject($object, $format = null, $locale = null)
    {
        $pattern = $calendar = null;

        if (is_array($format)) {
            list($dateFormat, $timeFormat) = $format;
        } elseif (is_numeric($format)) {
            $dateFormat = $format;
            $timeFormat = \IntlDateFormatter::FULL;
        } elseif (is_string($format) || null === $format) {
            $dateFormat = $timeFormat = \IntlDateFormatter::MEDIUM;
            $pattern = $format;
        } else {
            throw new LocalizedException(
                new Phrase('The format type is invalid. Verify the format type and try again.')
            );
        }

        $timezone = $object->getTimezone();
        if ($object instanceof \IntlCalendar) {
            $timezone = $timezone->toDateTimeZone();
        }
        $timezone = $timezone->getName();

        if ($timezone === '+00:00') {
            $timezone = 'UTC';
        } elseif ($timezone[0] === '+' || $timezone[0] === '-') { // $timezone[0] is first symbol of string
            $timezone = 'GMT' . $timezone;
        }

        return (new \IntlDateFormatter($locale, $dateFormat, $timeFormat, $timezone, $calendar, $pattern))
            ->format($object);
    }
}
