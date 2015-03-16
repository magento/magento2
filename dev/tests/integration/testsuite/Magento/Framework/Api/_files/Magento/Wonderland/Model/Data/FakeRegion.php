<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wonderland\Model\Data;

use Magento\Framework\Api\AbstractExtensibleObject;

class FakeRegion extends AbstractExtensibleObject implements \Magento\Wonderland\Api\Data\FakeRegionInterface
{
    /**
     * Get region
     *
     * @return string
     */
    public function getRegion()
    {
        return $this->_get(self::REGION);
    }

    /**
     * Get region code
     *
     * @return string
     */
    public function getRegionCode()
    {
        return $this->_get(self::REGION_CODE);
    }

    /**
     * Get region id
     *
     * @return int
     */
    public function getRegionId()
    {
        return $this->_get(self::REGION_ID);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Wonderland\Api\Data\FakeRegionExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Wonderland\Api\Data\FakeRegionExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Wonderland\Api\Data\FakeRegionExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Set region code
     *
     * @param string $regionCode
     * @return $this
     */
    public function setRegionCode($regionCode)
    {
        return $this->setData(self::REGION_CODE, $regionCode);
    }
}
