<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Directory\Model;

use Magento\Directory\Helper\Data as DataHelper;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;

class RegionProvider
{
    /**
     * @var RegionsArray
     */
    private $regions;
    /**
     * @var DataHelper
     */
    private $directoryHelper;
    /**
     * @var JsonSerializer
     */
    private $jsonSerializer;

    /**
     * Constructor
     *
     * @param DataHelper $directoryHelper
     * @param JsonSerializer $jsonSerializer
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        DataHelper $directoryHelper,
        JsonSerializer $jsonSerializer
    ) {
        $this->directoryHelper= $directoryHelper;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * Get region data json
     *
     * @return string
     */
    public function getRegionJson(): string
    {
        $regions = $this->getRegions();
        return $this->jsonSerializer->serialize($regions);
    }

    /**
     * Get regions array
     *
     * @return array
     */
    private function getRegions() : array
    {
        if (!$this->regions) {
            $regions = $this->directoryHelper->getRegionData();
            $this->regions['config'] = $regions['config'];
            unset($regions['config']);
            foreach ($regions as $countryCode => $countryRegions) {
                foreach ($countryRegions as $regionId => $regionData) {
                    $this->regions[$countryCode][] = [
                        'id'   => $regionId,
                        'name' => $regionData['name'],
                        'code' => $regionData['code']
                    ];
                }
            }
        }
        return $this->regions;
    }
}
