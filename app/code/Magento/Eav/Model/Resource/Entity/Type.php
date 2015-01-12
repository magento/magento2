<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Resource\Entity;

/**
 * EAV entity type resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Type extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('eav_entity_type', 'entity_type_id');
    }

    /**
     * Load Entity Type by Code
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param string $code
     * @return $this
     */
    public function loadByCode($object, $code)
    {
        return $this->load($object, $code, 'entity_type_code');
    }

    /**
     * Retrieve additional attribute table name for specified entity type
     *
     * @param integer $entityTypeId
     * @return string
     */
    public function getAdditionalAttributeTable($entityTypeId)
    {
        $adapter = $this->_getReadAdapter();
        $bind = ['entity_type_id' => $entityTypeId];
        $select = $adapter->select()->from(
            $this->getMainTable(),
            ['additional_attribute_table']
        )->where(
            'entity_type_id = :entity_type_id'
        );

        return $adapter->fetchOne($select, $bind);
    }
}
