<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Model;

/**
 * Local date for catalog rule
 */
class RuleDateFormatter implements \Magento\CatalogRule\Model\RuleDateFormatterInterface
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $localeDate;

    /**
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     */
    public function __construct(\Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate)
    {
        $this->localeDate = $localeDate;
    }

    /**
     * @inheritdoc
     */
    public function getDate($scope = null): \DateTime
    {
        return $this->localeDate->scopeDate($scope, null, true);
    }

    /**
     * @inheritdoc
     */
    public function getTimeStamp($scope = null): int
    {
        return $this->localeDate->scopeTimeStamp($scope);
    }
}
