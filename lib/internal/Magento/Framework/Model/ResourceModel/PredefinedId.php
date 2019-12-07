<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;

/**
 * Provides possibility of saving entity with predefined/pre-generated id
 *
 * The choice to use trait instead of inheritance was made to prevent the introduction of new layer super type on
 * the module basis as well as better code reusability, as potentially current trait not coupled to Inventory module
 * and other modules could re-use this approach.
 */
trait PredefinedId
{
    /**
     * Overwrite default \Magento\Framework\Model\ResourceModel\Db\AbstractDb implementation of the isObjectNew
     * @see \Magento\Framework\Model\ResourceModel\Db\AbstractDb::isObjectNew()
     *
     * Adding the possibility to check whether record already exists in DB or not
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
     * Overwrite default \Magento\Framework\Model\ResourceModel\Db\AbstractDb implementation of the saveNewObject
     * @see \Magento\Framework\Model\ResourceModel\Db\AbstractDb::saveNewObject()
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
