<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Form\Element\DataType;

use Exception;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\Stdlib\DateTime\Intl\DateFormatterFactory;
use Magento\Framework\App\ObjectManager;

/**
 * UI component date type
 */
class Date extends AbstractDataType
{
    public const NAME = 'date';

    /**
     * Current locale
     *
     * @var string
     */
    protected $locale;

    /**
     * Wrapped component for date type
     *
     * @var UiComponentInterface
     */
    protected $wrappedComponent;

    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * @var DateFormatterFactory
     */
    private $dateFormatterFactory;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param TimezoneInterface $localeDate
     * @param ResolverInterface $localeResolver
     * @param array $components
     * @param array $data
     * @param DateFormatterFactory|null $dateFormatterFactory
     */
    public function __construct(
        ContextInterface $context,
        TimezoneInterface $localeDate,
        ResolverInterface $localeResolver,
        array $components = [],
        array $data = [],
        ?DateFormatterFactory $dateFormatterFactory = null
    ) {
        $this->locale = $localeResolver->getLocale();
        $this->localeDate = $localeDate;
        parent::__construct($context, $components, $data);
        $objectManager = ObjectManager::getInstance();
        $this->dateFormatterFactory = $dateFormatterFactory ?? $objectManager->get(DateFormatterFactory::class);
    }

    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {
        $config = $this->getData('config');

        if (!isset($config['storeTimeZone'])) {
            $storeTimeZone = $this->localeDate->getConfigTimezone();
            $config['storeTimeZone'] = $storeTimeZone;
        }
        // Set date format pattern by current locale
        $localeDateFormat = $this->localeDate->getDateFormat();
        $config['options']['dateFormat'] = $localeDateFormat;
        $config['options']['storeLocale'] = $this->locale;
        $this->setData('config', $config);
        parent::prepare();
    }

    /**
     * Get locale
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Get component name
     *
     * @return string
     */
    public function getComponentName()
    {
        return static::NAME;
    }

    /**
     * Convert given date to default (UTC) timezone
     *
     * @param int $date
     * @param int $hour
     * @param int $minute
     * @param int $second
     * @param bool $setUtcTimeZone
     * @return \DateTime|null
     */
    public function convertDate($date, $hour = 0, $minute = 0, $second = 0, $setUtcTimeZone = true)
    {
        try {
            $date = $this->convertDateFormat($date);
            $dateObj = $this->localeDate->date($date, $this->getLocale(), false, false);
            $dateObj->setTime($hour, $minute, $second);
            //convert store date to default date in UTC timezone without DST
            if ($setUtcTimeZone) {
                $dateObj->setTimezone(new \DateTimeZone('UTC'));
            }
            return $dateObj;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Convert given date to default (UTC) timezone
     *
     * @param string $date
     * @param bool $setUtcTimezone
     * @return \DateTime|null
     */
    public function convertDatetime(string $date, bool $setUtcTimezone = true): ?\DateTime
    {
        try {
            $date = rtrim($date, 'Z');
            $dateObj = new \DateTime($date, new \DateTimeZone($this->localeDate->getConfigTimezone()));
            //convert store date to default date in UTC timezone without DST
            if ($setUtcTimezone) {
                $dateObj->setTimezone(new \DateTimeZone('UTC'));
            }
            return $dateObj;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Convert given date to specific date format based on locale
     *
     * @param string $date
     * @return String
     * @throws Exception
     */
    public function convertDateFormat(string $date): String
    {
        $formatter = $this->dateFormatterFactory->create(
            $this->getLocale(),
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
        return $date;
    }
}
