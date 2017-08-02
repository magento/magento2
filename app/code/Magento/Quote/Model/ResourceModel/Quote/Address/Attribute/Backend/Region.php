<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\ResourceModel\Quote\Address\Attribute\Backend;

/**
 * Quote address attribute backend region resource model
 * @since 2.0.0
 */
class Region extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * @var \Magento\Directory\Model\RegionFactory
     * @since 2.0.0
     */
    protected $_regionFactory;

    /**
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @since 2.0.0
     */
    public function __construct(\Magento\Directory\Model\RegionFactory $regionFactory)
    {
        $this->_regionFactory = $regionFactory;
    }

    /**
     * Set region to the attribute
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     * @since 2.0.0
     */
    public function beforeSave($object)
    {
        if (is_numeric($object->getRegion())) {
            $region = $this->_regionFactory->create()->load((int)$object->getRegion());
            if ($region) {
                $object->setRegionId($region->getId());
                $object->setRegion($region->getCode());
            }
        }

        return $this;
    }
}
