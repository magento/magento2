<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\ResourceModel\Address\Attribute\Backend;

/**
 * Address region attribute backend
 *
 * @author      Magento Core Team <core@magentocommerce.com>
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
     * Prepare object for save
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     * @since 2.0.0
     */
    public function beforeSave($object)
    {
        $region = $object->getData('region');
        if (is_numeric($region)) {
            $regionModel = $this->_createRegionInstance();
            $regionModel->load($region);
            if ($regionModel->getId() && $object->getCountryId() == $regionModel->getCountryId()) {
                $object->setRegionId($regionModel->getId())->setRegion($regionModel->getName());
            }
        }
        return $this;
    }

    /**
     * @return \Magento\Directory\Model\Region
     * @since 2.0.0
     */
    protected function _createRegionInstance()
    {
        return $this->_regionFactory->create();
    }
}
