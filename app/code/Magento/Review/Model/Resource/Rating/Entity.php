<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Model\Resource\Rating;

/**
 * Rating entity resource
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Entity extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Rating entity resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('rating_entity', 'entity_id');
    }

    /**
     * Return entity_id by entityCode
     *
     * @param string $entityCode
     * @return int
     */
    public function getIdByCode($entityCode)
    {
        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()->from(
            $this->getTable('rating_entity'),
            $this->getIdFieldName()
        )->where(
            'entity_code = :entity_code'
        );
        return $adapter->fetchOne($select, [':entity_code' => $entityCode]);
    }
}
