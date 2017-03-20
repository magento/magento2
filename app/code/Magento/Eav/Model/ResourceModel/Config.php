<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\ResourceModel;

/**
 * Eav Resource Config model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Config extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Array of entity types
     *
     * @var array
     */
    protected static $_entityTypes = [];

    /**
     * Array of attributes
     *
     * @var array
     */
    protected static $_attributes = [];

    /**
     * Resource initialization
     *
     * @return void
     * @codeCoverageIgnore
     */
    protected function _construct()
    {
        $this->_init('eav_entity_type', 'entity_type_id');
    }

    /**
     * Load all entity types
     *
     * @return $this
     */
    protected function _loadTypes()
    {
        $connection = $this->getConnection();
        if (!$connection) {
            return $this;
        }
        if (empty(self::$_entityTypes)) {
            $select = $connection->select()->from($this->getMainTable());
            $data = $connection->fetchAll($select);
            foreach ($data as $row) {
                self::$_entityTypes['by_id'][$row['entity_type_id']] = $row;
                self::$_entityTypes['by_code'][$row['entity_type_code']] = $row;
            }
        }

        return $this;
    }

    /**
     * Load attribute types
     *
     * @param int $typeId
     * @return array
     */
    protected function _loadTypeAttributes($typeId)
    {
        if (!isset(self::$_attributes[$typeId])) {
            $connection = $this->getConnection();
            $bind = ['entity_type_id' => $typeId];
            $select = $connection->select()->from(
                $this->getTable('eav_attribute')
            )->where(
                'entity_type_id = :entity_type_id'
            );

            self::$_attributes[$typeId] = $connection->fetchAll($select, $bind);
        }

        return self::$_attributes[$typeId];
    }

    /**
     * Retrieve entity type data
     *
     * @param string $entityType
     * @return array
     */
    public function fetchEntityTypeData($entityType)
    {
        $this->_loadTypes();

        if (is_numeric($entityType)) {
            $info = isset(
                self::$_entityTypes['by_id'][$entityType]
            ) ? self::$_entityTypes['by_id'][$entityType] : null;
        } else {
            $info = isset(
                self::$_entityTypes['by_code'][$entityType]
            ) ? self::$_entityTypes['by_code'][$entityType] : null;
        }

        $data = [];
        if ($info) {
            $data['entity'] = $info;
            $attributes = $this->_loadTypeAttributes($info['entity_type_id']);
            $data['attributes'] = [];
            foreach ($attributes as $attribute) {
                $data['attributes'][$attribute['attribute_id']] = $attribute;
                $data['attributes'][$attribute['attribute_code']] = $attribute['attribute_id'];
            }
        }

        return $data;
    }
}
