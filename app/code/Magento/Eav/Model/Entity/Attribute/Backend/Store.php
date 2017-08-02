<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity\Attribute\Backend;

/**
 * @api
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
     * @codeCoverageIgnore
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function _beforeSave($object)
    {
        if (!$object->getData($this->getAttribute()->getAttributeCode())) {
            $object->setData($this->getAttribute()->getAttributeCode(), $this->_storeManager->getStore()->getId());
        }

        return $this;
    }
}
