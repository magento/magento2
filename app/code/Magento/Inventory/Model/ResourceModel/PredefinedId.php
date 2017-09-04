<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;

/**
 * Provide possibility of save entity with predefined id
 * Use trait instead of extending for better reusability, also we don'nt want to hardcode hierarchy of inheritance
 */
trait PredefinedId
{
    /**
     * Check if object is new
     *
     * @param AbstractModel $object
     * @return bool
     */
    protected function isObjectNotNew(AbstractModel $object)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable(), [$this->getIdFieldName()])
            ->where($this->getIdFieldName() . ' = ?', $object->getId())
            ->limit(1);
        return (bool)$connection->fetchOne($select);
    }

    /**
     * Save New Object
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @throws LocalizedException
     * @return void
     */
    protected function saveNewObject(\Magento\Framework\Model\AbstractModel $object)
    {
        $bind = $this->_prepareDataForSave($object);
        $this->getConnection()->insert($this->getMainTable(), $bind);

        if ($this->_isPkAutoIncrement) {
            $object->setId($this->getConnection()->lastInsertId($this->getMainTable()));
        }

        if ($this->_useIsObjectNew) {
            $object->isObjectNew(false);
        }
    }
}
