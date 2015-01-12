<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Resource\Quote\Address\Attribute\Backend;

/**
 * Quote address attribute backend region resource model
 */
class Region extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $_regionFactory;

    /**
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     */
    public function __construct(\Magento\Directory\Model\RegionFactory $regionFactory)
    {
        $this->_regionFactory = $regionFactory;
    }

    /**
     * Set region to the attribute
     *
     * @param \Magento\Framework\Object $object
     * @return $this
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
