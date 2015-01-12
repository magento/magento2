<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Core\Model\Resource;

use Magento\Framework\Stdlib\DateTime;

/**
 * Core Design Resource Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Design extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param DateTime $dateTime
     */
    public function __construct(\Magento\Framework\App\Resource $resource, DateTime $dateTime)
    {
        $this->dateTime = $dateTime;
        parent::__construct($resource);
    }

    /**
     * Define main table and primary key
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('design_change', 'design_change_id');
    }

    /**
     * Perform actions before object save
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Model\Exception
     */
    public function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($date = $object->getDateFrom()) {
            $object->setDateFrom($this->dateTime->formatDate($date));
        } else {
            $object->setDateFrom(null);
        }

        if ($date = $object->getDateTo()) {
            $object->setDateTo($this->dateTime->formatDate($date));
        } else {
            $object->setDateTo(null);
        }

        if (!is_null(
            $object->getDateFrom()
        ) && !is_null(
            $object->getDateTo()
        ) && $this->dateTime->toTimestamp(
            $object->getDateFrom()
        ) > $this->dateTime->toTimestamp(
            $object->getDateTo()
        )
        ) {
            throw new \Magento\Framework\Model\Exception(__('Start date cannot be greater than end date.'));
        }

        $check = $this->_checkIntersection(
            $object->getStoreId(),
            $object->getDateFrom(),
            $object->getDateTo(),
            $object->getId()
        );

        if ($check) {
            throw new \Magento\Framework\Model\Exception(
                __(
                    'Your design change for the specified store intersects with another one, please specify another date range.'
                )
            );
        }

        if ($object->getDateFrom() === null) {
            $object->setDateFrom(new \Zend_Db_Expr('null'));
        }
        if ($object->getDateTo() === null) {
            $object->setDateTo(new \Zend_Db_Expr('null'));
        }

        parent::_beforeSave($object);
    }

    /**
     * Check intersections
     *
     * @param int $storeId
     * @param string $dateFrom
     * @param string $dateTo
     * @param int $currentId
     * @return string
     */
    protected function _checkIntersection($storeId, $dateFrom, $dateTo, $currentId)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()->from(
            ['main_table' => $this->getTable('design_change')]
        )->where(
            'main_table.store_id = :store_id'
        )->where(
            'main_table.design_change_id <> :current_id'
        );

        $dateConditions = ['date_to IS NULL AND date_from IS NULL'];

        if (!is_null($dateFrom)) {
            $dateConditions[] = ':date_from BETWEEN date_from AND date_to';
            $dateConditions[] = ':date_from >= date_from and date_to IS NULL';
            $dateConditions[] = ':date_from <= date_to and date_from IS NULL';
        } else {
            $dateConditions[] = 'date_from IS NULL';
        }

        if (!is_null($dateTo)) {
            $dateConditions[] = ':date_to BETWEEN date_from AND date_to';
            $dateConditions[] = ':date_to >= date_from AND date_to IS NULL';
            $dateConditions[] = ':date_to <= date_to AND date_from IS NULL';
        } else {
            $dateConditions[] = 'date_to IS NULL';
        }

        if (is_null($dateFrom) && !is_null($dateTo)) {
            $dateConditions[] = 'date_to <= :date_to OR date_from <= :date_to';
        }

        if (!is_null($dateFrom) && is_null($dateTo)) {
            $dateConditions[] = 'date_to >= :date_from OR date_from >= :date_from';
        }

        if (!is_null($dateFrom) && !is_null($dateTo)) {
            $dateConditions[] = 'date_from BETWEEN :date_from AND :date_to';
            $dateConditions[] = 'date_to BETWEEN :date_from AND :date_to';
        } elseif (is_null($dateFrom) && is_null($dateTo)) {
            $dateConditions = [];
        }

        $condition = '';
        if (!empty($dateConditions)) {
            $condition = '(' . implode(') OR (', $dateConditions) . ')';
            $select->where($condition);
        }

        $bind = ['store_id' => (int)$storeId, 'current_id' => (int)$currentId];

        if (!is_null($dateTo)) {
            $bind['date_to'] = $dateTo;
        }
        if (!is_null($dateFrom)) {
            $bind['date_from'] = $dateFrom;
        }

        $result = $adapter->fetchOne($select, $bind);
        return $result;
    }

    /**
     * Load changes for specific store and date
     *
     * @param int $storeId
     * @param string $date
     * @return array
     */
    public function loadChange($storeId, $date)
    {
        $select = $this->_getReadAdapter()->select()->from(
            ['main_table' => $this->getTable('design_change')]
        )->where(
            'store_id = :store_id'
        )->where(
            'date_from <= :required_date or date_from IS NULL'
        )->where(
            'date_to >= :required_date or date_to IS NULL'
        );

        $bind = ['store_id' => (int)$storeId, 'required_date' => $date];

        return $this->_getReadAdapter()->fetchRow($select, $bind);
    }
}
