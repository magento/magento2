<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Stdlib\DateTime\Filter;

use Magento\Framework\Locale\Resolver;
use Magento\Framework\Exception\LocalizedException;

/**
 * Date/Time filter. Converts datetime from localized to internal format.
 *
 * @api
 */
class DateTime extends Date
{
    /**
     * @var Resolve
     */
    private $localeResolver;

    /**
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     *
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
    ) {
        parent::__construct($localeDate);
        $this->_localToNormalFilter = new \Zend_Filter_LocalizedToNormalized(
            [
                'date_format' => $this->_localeDate->getDateTimeFormat(
                    \IntlDateFormatter::SHORT
                ),
            ]
        );
        $this->_normalToLocalFilter = new \Zend_Filter_NormalizedToLocalized(
            ['date_format' => \Magento\Framework\Stdlib\DateTime::DATETIME_INTERNAL_FORMAT]
        );
    }

    /**
     * Get locale resolver
     *
     * @return \Magento\Framework\Locale\ResolverInterface|mixed
     */
    private function getLocaleResolver()
    {
        if ($this->localeResolver === null) {
            $this->localeResolver = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Locale\ResolverInterface::class
            );
        }
        return $this->localeResolver;
    }

    /**
     * Convert date from localized to internal format
     *
     * @param string $value
     * @return string
     * @throws \Exception
     * @since 100.1.0
     */
    public function filter($value)
    {
        $currentLocaleCode = $this->getLocaleResolver()->getLocale(); //retruning value zh_Hans_CN, but we need zh_CN
        if (strlen($currentLocaleCode>5)) {
            $languageCode = explode('_', $currentLocaleCode);
            $useCode = $languageCode[0].'_'.$languageCode[2];
        } else {
            $useCode = $currentLocaleCode;
        }

        try {
            $value = $this->_localeDate->formatDateTime(
                $value,
                \IntlDateFormatter::SHORT,
                \IntlDateFormatter::SHORT,
                $useCode,
                null,
                null
            );
            $dateTime = $this->_localeDate->date($value, null, false);
            return $dateTime->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            throw new LocalizedException("Invalid input datetime format of value '$value'",
                $e->getCode(),
                $e
            );
        }
    }
}
