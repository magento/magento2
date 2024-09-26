<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter\FieldType;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * @deprecated Elasticsearch is no longer supported by Adobe
 * @see this class will be responsible for ES only
 */
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
     * Retrieve date value in elasticsearch format (ISO 8601)
     *
     * Example: 1995-12-31T23:59:59
     *
     * @param int $storeId
     * @param string|null $date
     * @return string|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function formatDate($storeId, $date = null)
    {
        if ($this->dateTime->isEmptyDate($date)) {
            return null;
        }
        $dateObj = new \DateTime($date, new \DateTimeZone('UTC'));
        return $dateObj->format('c');
    }
}
