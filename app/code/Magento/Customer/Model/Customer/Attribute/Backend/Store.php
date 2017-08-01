<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Customer\Attribute\Backend;

/**
 * Store attribute backend
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Store extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $_storeManager;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @since 2.0.0
     */
    public function __construct(\Magento\Store\Model\StoreManagerInterface $storeManager)
    {
        $this->_storeManager = $storeManager;
    }

    /**
     * Before save
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     * @since 2.0.0
     */
    public function beforeSave($object)
    {
        if ($object->getId()) {
            return $this;
        }

        if (!$object->hasStoreId()) {
            $object->setStoreId($this->_storeManager->getStore()->getId());
        }

        if (!$object->hasData('created_in')) {
            $object->setData('created_in', $this->_storeManager->getStore($object->getStoreId())->getName());
        }

        return $this;
    }
}
