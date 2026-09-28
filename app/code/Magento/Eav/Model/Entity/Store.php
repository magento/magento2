<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Eav\Model\Entity;

/**
 * @api
 * @method int getEntityTypeId()
 * @method \Magento\Eav\Model\Entity\Store setEntityTypeId(int $value)
 * @method int getStoreId()
 * @method \Magento\Eav\Model\Entity\Store setStoreId(int $value)
 * @method string getIncrementPrefix()
 * @method \Magento\Eav\Model\Entity\Store setIncrementPrefix(string $value)
 * @method string getIncrementLastId()
 * @method \Magento\Eav\Model\Entity\Store setIncrementLastId(string $value)
 * @since 100.0.2
 */
class Store extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Resource initialization
     *
     * @return void
     * @codeCoverageIgnore
     */
    protected function _construct()
    {
        $this->_init(\Magento\Eav\Model\ResourceModel\Entity\Store::class);
    }

    /**
     * Load entity by store
     *
     * @param int $entityTypeId
     * @param int $storeId
     * @return $this
     * @codeCoverageIgnore
     */
    public function loadByEntityStore($entityTypeId, $storeId)
    {
        $this->_getResource()->loadByEntityStore($this, $entityTypeId, $storeId);
        return $this;
    }
}
