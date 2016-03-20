<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity\Attribute\Backend;

class Store extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @codeCoverageIgnore
     */
    public function __construct(\Magento\Store\Model\StoreManagerInterface $storeManager)
    {
        $this->_storeManager = $storeManager;
    }

    /**
     * Prepare data before save
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    protected function _beforeSave($object)
    {
        if (!$object->getData($this->getAttribute()->getAttributeCode())) {
            $object->setData($this->getAttribute()->getAttributeCode(), $this->_storeManager->getStore()->getId());
        }

        return $this;
    }
}
