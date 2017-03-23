<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\ResourceModel\Entity;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject;

/**
 * Eav Entity store resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Store extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     * @codeCoverageIgnore
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
        $connection = $this->getConnection();
        $bind = [':entity_type_id' => $entityTypeId, ':store_id' => $storeId];
        $select = $connection->select()->from(
            $this->getMainTable()
        )->forUpdate(
            true
        )->where(
            'entity_type_id = :entity_type_id'
        )->where(
            'store_id = :store_id'
        );
        $data = $connection->fetchRow($select, $bind);

        if (!$data) {
            return false;
        }

        $object->setData($data);

        $this->_afterLoad($object);

        return true;
    }
}
