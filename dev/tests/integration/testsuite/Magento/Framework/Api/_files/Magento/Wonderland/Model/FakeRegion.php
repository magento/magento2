<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wonderland\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Wonderland\Api\Data\FakeRegionInterface;

class FakeRegion extends AbstractExtensibleModel implements FakeRegionInterface
{
    /**
     * Get region
     *
     * @return string
     */
    public function getRegion()
    {
        return $this->getData(self::REGION);
    }

    /**
     * Get region code
     *
     * @return string
     */
    public function getRegionCode()
    {
        return $this->getData(self::REGION_CODE);
    }

    /**
     * Get region id
     *
     * @return int
     */
    public function getRegionId()
    {
        return $this->getData(self::REGION_ID);
    }
}
