<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Resource\Entity;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Object;

/**
 * Eav Entity store resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Store extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('eav_entity_store', 'entity_store_id');
    }

    /**
     * Load an object by entity type and store
     *
     * @param Object|\Magento\Framework\Model\AbstractModel $object
     * @param int $entityTypeId
     * @param int $storeId
     * @return bool
     */
    public function loadByEntityStore(AbstractModel $object, $entityTypeId, $storeId)
    {
        $adapter = $this->_getWriteAdapter();
        $bind = [':entity_type_id' => $entityTypeId, ':store_id' => $storeId];
        $select = $adapter->select()->from(
            $this->getMainTable()
        )->forUpdate(
            true
        )->where(
            'entity_type_id = :entity_type_id'
        )->where(
            'store_id = :store_id'
        );
        $data = $adapter->fetchRow($select, $bind);

        if (!$data) {
            return false;
        }

        $object->setData($data);

        $this->_afterLoad($object);

        return true;
    }
}
