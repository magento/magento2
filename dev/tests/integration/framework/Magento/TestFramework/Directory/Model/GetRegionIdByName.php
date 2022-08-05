<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Directory\Model;

use Magento\Directory\Model\RegionFactory;

/**
 * Return region ID by region default name and country code.
 */
class GetRegionIdByName
{
    /**
     * @var RegionFactory
     */
    private $regionFactory;

    /**
     * @var array
     */
    private $regionIdsCache;

    /**
     * @param RegionFactory $regionFactory
     */
    public function __construct(
        RegionFactory $regionFactory
    ) {
        $this->regionFactory = $regionFactory;
    }

    /**
     * Get region ID from cache property if region id exist or load it.
     *
     * @param string $regionName
     * @param string $countryId
     * @return int|null
     */
    public function execute(string $regionName, string $countryId): ?int
    {
        $cacheKey = "{$regionName}_{$countryId}";

        if (!isset($this->regionIdsCache[$cacheKey])) {
            $region = $this->regionFactory->create()->loadByName($regionName, $countryId);
            $this->regionIdsCache[$cacheKey] = $region->getRegionId() ? (int)$region->getRegionId() : null;
        }

        return $this->regionIdsCache[$cacheKey];
    }
}
