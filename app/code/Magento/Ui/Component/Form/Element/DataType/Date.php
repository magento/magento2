<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Form\Element\DataType;

use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class Date
 * @since 2.0.0
 */
class Date extends AbstractDataType
{
    const NAME = 'date';

    /**
     * Current locale
     *
     * @var string
     * @since 2.0.0
     */
    protected $locale;

    /**
     * Wrapped component
     *
     * @var UiComponentInterface
     * @since 2.0.0
     */
    protected $wrappedComponent;

    /**
     * @var TimezoneInterface
     * @since 2.2.0
     */
    private $localeDate;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param TimezoneInterface $localeDate
     * @param ResolverInterface $localeResolver
     * @param array $components
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        ContextInterface $context,
        TimezoneInterface $localeDate,
        ResolverInterface $localeResolver,
        array $components = [],
        array $data = []
    ) {
        $this->locale = $localeResolver->getLocale();
        $this->localeDate = $localeDate;
        parent::__construct($context, $components, $data);
    }

    /**
     * Prepare component configuration
     *
     * @return void
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Get component name
     *
     * @return string
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function convertDate($date, $hour = 0, $minute = 0, $second = 0, $setUtcTimeZone = true)
    {
        try {
            $dateObj = $this->localeDate->date(
                new \DateTime(
                    $date,
                    new \DateTimeZone($this->localeDate->getConfigTimezone())
                ),
                $this->getLocale(),
                true
            );
            $dateObj->setTime($hour, $minute, $second);
            //convert store date to default date in UTC timezone without DST
            if ($setUtcTimeZone) {
                $dateObj->setTimezone(new \DateTimeZone('UTC'));
            }
            return $dateObj;
        } catch (\Exception $e) {
            return null;
        }
    }
}
