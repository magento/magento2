<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Stdlib\DateTime\Filter;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Date/Time filter. Converts datetime from localized to internal format.
 *
 * @api
 * @since 100.0.2
 */
class DateTime extends Date
{
    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @param TimezoneInterface $localeDate
     * @param ResolverInterface|null $localeResolver
     */
    public function __construct(
        TimezoneInterface $localeDate,
        ?ResolverInterface $localeResolver = null
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
        $this->localeResolver = $localeResolver ?: ObjectManager::getInstance()->create(ResolverInterface::class);
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
        $currentLocaleCode = $this->localeResolver->getLocale();

        try {
            $value = $this->_localeDate->formatDateTime(
                $value,
                \IntlDateFormatter::SHORT,
                \IntlDateFormatter::SHORT,
                $currentLocaleCode,
                null,
                null
            );

            $dateTime = $this->_localeDate->date($value, null, false);
            return $dateTime->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            throw new InputException(__("Invalid input datetime format of value '$value'", $e->getCode(), $e));
        }
    }
}
