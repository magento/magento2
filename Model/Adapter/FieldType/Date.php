<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter\FieldType;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\ScopeInterface;

class Date
{
    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Array of \DateTime objects per store
     *
     * @var \DateTime[]
     */
    protected $dateFormats = [];

    /**
     * Construct
     *
     * @param DateTime $dateTime
     * @param TimezoneInterface $localeDate
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        DateTime $dateTime,
        TimezoneInterface $localeDate,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->dateTime = $dateTime;
        $this->localeDate = $localeDate;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Retrieve date value in elasticsearch format (ISO 8601) with Z
     * Example: 1995-12-31T23:59:59Z
     *
     * @param int $storeId
     * @param string|null $date
     * @return string|null
     */
    public function formatDate($storeId, $date = null)
    {
        if ($this->dateTime->isEmptyDate($date)) {
            return null;
        }
        if (!array_key_exists($storeId, $this->dateFormats)) {
            $timezone = $this->scopeConfig->getValue(
                $this->localeDate->getDefaultTimezonePath(),
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
            $dateObj = new \DateTime();
            $dateObj->setTimezone(new \DateTimeZone($timezone));
            $this->dateFormats[$storeId] = $dateObj;
        }
        $dateObj = $this->dateFormats[$storeId];
        return $dateObj->format('c');
    }
}
