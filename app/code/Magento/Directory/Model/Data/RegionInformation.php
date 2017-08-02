<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Model\Data;

/**
 * Class Region Information
 *
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class RegionInformation extends \Magento\Framework\Api\AbstractExtensibleObject implements
    \Magento\Directory\Api\Data\RegionInformationInterface
{
    const KEY_REGION_ID   = 'region_id';
    const KEY_REGION_CODE = 'region_code';
    const KEY_REGION_NAME = 'region_name';

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function getId()
    {
        return $this->_get(self::KEY_REGION_ID);
    }

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function setId($regionId)
    {
        $this->setData(self::KEY_REGION_ID, $regionId);
    }

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function getCode()
    {
        return $this->_get(self::KEY_REGION_CODE);
    }

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function setCode($regionCode)
    {
        $this->setData(self::KEY_REGION_CODE, $regionCode);
    }

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function getName()
    {
        return $this->_get(self::KEY_REGION_NAME);
    }

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function setName($regionName)
    {
        $this->setData(self::KEY_REGION_NAME, $regionName);
    }

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Directory\Api\Data\RegionInformationExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
