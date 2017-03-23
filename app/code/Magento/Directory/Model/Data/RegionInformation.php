<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Model\Data;

/**
 * Class Region Information
 *
 * @codeCoverageIgnore
 */
class RegionInformation extends \Magento\Framework\Api\AbstractExtensibleObject implements
    \Magento\Directory\Api\Data\RegionInformationInterface
{
    const KEY_REGION_ID   = 'region_id';
    const KEY_REGION_CODE = 'region_code';
    const KEY_REGION_NAME = 'region_name';

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return $this->_get(self::KEY_REGION_ID);
    }

    /**
     * @inheritDoc
     */
    public function setId($regionId)
    {
        $this->setData(self::KEY_REGION_ID, $regionId);
    }

    /**
     * @inheritDoc
     */
    public function getCode()
    {
        return $this->_get(self::KEY_REGION_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setCode($regionCode)
    {
        $this->setData(self::KEY_REGION_CODE, $regionCode);
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->_get(self::KEY_REGION_NAME);
    }

    /**
     * @inheritDoc
     */
    public function setName($regionName)
    {
        $this->setData(self::KEY_REGION_NAME, $regionName);
    }

    /**
     * @inheritDoc
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritDoc
     */
    public function setExtensionAttributes(
        \Magento\Directory\Api\Data\RegionInformationExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
