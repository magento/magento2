<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Model\ResourceModel\Rating;

/**
 * Rating entity resource
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Entity extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
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
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            $this->getTable('rating_entity'),
            $this->getIdFieldName()
        )->where(
            'entity_code = :entity_code'
        );
        return $connection->fetchOne($select, [':entity_code' => $entityCode]);
    }
}
