<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Data;

/**
 * Data Model implementing Address Region interface
 *
 * @since 2.0.0
 */
class Region extends \Magento\Framework\Api\AbstractExtensibleObject implements
    \Magento\Customer\Api\Data\RegionInterface
{
    /**
     * Get region code
     *
     * @return string
     * @since 2.0.0
     */
    public function getRegionCode()
    {
        return $this->_get(self::REGION_CODE);
    }

    /**
     * Get region
     *
     * @return string
     * @since 2.0.0
     */
    public function getRegion()
    {
        return $this->_get(self::REGION);
    }

    /**
     * Get region id
     *
     * @return int
     * @since 2.0.0
     */
    public function getRegionId()
    {
        return $this->_get(self::REGION_ID);
    }

    /**
     * Set region code
     *
     * @param string $regionCode
     * @return $this
     * @since 2.0.0
     */
    public function setRegionCode($regionCode)
    {
        return $this->setData(self::REGION_CODE, $regionCode);
    }

    /**
     * Set region
     *
     * @param string $region
     * @return $this
     * @since 2.0.0
     */
    public function setRegion($region)
    {
        return $this->setData(self::REGION, $region);
    }

    /**
     * Set region id
     *
     * @param int $regionId
     * @return $this
     * @since 2.0.0
     */
    public function setRegionId($regionId)
    {
        return $this->setData(self::REGION_ID, $regionId);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Customer\Api\Data\RegionExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Customer\Api\Data\RegionExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(\Magento\Customer\Api\Data\RegionExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
