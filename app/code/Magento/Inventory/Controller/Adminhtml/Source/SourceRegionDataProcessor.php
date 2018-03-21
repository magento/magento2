<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Controller\Adminhtml\Source;

use Magento\Directory\Model\RegionFactory;
use Magento\Framework\Exception\InputException;

/**
 * Prepare region data.
 */
class SourceRegionDataProcessor
{
    /**
     * @var RegionFactory
     */
    private $regionFactory;

    /**
     * @param RegionFactory $regionFactory
     */
    public function __construct(
        RegionFactory $regionFactory
    ) {
        $this->regionFactory = $regionFactory;
    }

    /**
     * @param array $data
     *
     * @return array
     * @throws InputException
     */
    public function process(array $data): array
    {
        $regionId = $data['region_id'] ?? 0;

        if ($regionId != 0) {
            $region = $this->regionFactory->create();
            $region->load($regionId);
            $data['region'] = $region->getName();
        }

        return $data;
    }
}
