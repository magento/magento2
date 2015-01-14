<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity;

/**
 * @method \Magento\Eav\Model\Resource\Entity\Store _getResource()
 * @method \Magento\Eav\Model\Resource\Entity\Store getResource()
 * @method int getEntityTypeId()
 * @method \Magento\Eav\Model\Entity\Store setEntityTypeId(int $value)
 * @method int getStoreId()
 * @method \Magento\Eav\Model\Entity\Store setStoreId(int $value)
 * @method string getIncrementPrefix()
 * @method \Magento\Eav\Model\Entity\Store setIncrementPrefix(string $value)
 * @method string getIncrementLastId()
 * @method \Magento\Eav\Model\Entity\Store setIncrementLastId(string $value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Store extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Eav\Model\Resource\Entity\Store');
    }

    /**
     * Load entity by store
     *
     * @param int $entityTypeId
     * @param int $storeId
     * @return $this
     */
    public function loadByEntityStore($entityTypeId, $storeId)
    {
        $this->_getResource()->loadByEntityStore($this, $entityTypeId, $storeId);
        return $this;
    }
}
