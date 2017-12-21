<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Model\ResourceModel;

use Magento\Framework\Stdlib\DateTime;

/**
 * Design Change Resource Model
 */
class Design extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param DateTime $dateTime
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        DateTime $dateTime,
        $connectionName = null
    ) {
        $this->dateTime = $dateTime;
        parent::__construct($context, $connectionName);
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
     * @throws \Magento\Framework\Exception\LocalizedException
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

        if ($object->getDateFrom() !== null
            && $object->getDateTo() !== null
            && (new \DateTime($object->getDateFrom()))->getTimestamp()
            > (new \DateTime($object->getDateTo()))->getTimestamp()
        ) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The start date can\'t follow the end date.')
            );
        }

        $check = $this->_checkIntersection(
            $object->getStoreId(),
            $object->getDateFrom(),
            $object->getDateTo(),
            $object->getId()
        );

        if ($check) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The date range for this design change overlaps another design change for the specified store.')
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
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _checkIntersection($storeId, $dateFrom, $dateTo, $currentId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            ['main_table' => $this->getTable('design_change')]
        )->where(
            'main_table.store_id = :store_id'
        )->where(
            'main_table.design_change_id <> :current_id'
        );

        $dateConditions = ['date_to IS NULL AND date_from IS NULL'];

        if ($dateFrom !== null) {
            $dateConditions[] = ':date_from BETWEEN date_from AND date_to';
            $dateConditions[] = ':date_from >= date_from and date_to IS NULL';
            $dateConditions[] = ':date_from <= date_to and date_from IS NULL';
        } else {
            $dateConditions[] = 'date_from IS NULL';
        }

        if ($dateTo !== null) {
            $dateConditions[] = ':date_to BETWEEN date_from AND date_to';
            $dateConditions[] = ':date_to >= date_from AND date_to IS NULL';
            $dateConditions[] = ':date_to <= date_to AND date_from IS NULL';
        } else {
            $dateConditions[] = 'date_to IS NULL';
        }

        if ($dateFrom === null && $dateTo !== null) {
            $dateConditions[] = 'date_to <= :date_to OR date_from <= :date_to';
        }

        if ($dateFrom !== null && $dateTo === null) {
            $dateConditions[] = 'date_to >= :date_from OR date_from >= :date_from';
        }

        if ($dateFrom !== null && $dateTo !== null) {
            $dateConditions[] = 'date_from BETWEEN :date_from AND :date_to';
            $dateConditions[] = 'date_to BETWEEN :date_from AND :date_to';
        } elseif ($dateFrom === null && $dateTo === null) {
            $dateConditions = [];
        }

        $condition = '';
        if (!empty($dateConditions)) {
            $condition = '(' . implode(') OR (', $dateConditions) . ')';
            $select->where($condition);
        }

        $bind = ['store_id' => (int)$storeId, 'current_id' => (int)$currentId];

        if ($dateTo !== null) {
            $bind['date_to'] = $dateTo;
        }
        if ($dateFrom !== null) {
            $bind['date_from'] = $dateFrom;
        }

        $result = $connection->fetchOne($select, $bind);
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
        $select = $this->getConnection()->select()->from(
            ['main_table' => $this->getTable('design_change')]
        )->where(
            'store_id = :store_id'
        )->where(
            'date_from <= :required_date or date_from IS NULL'
        )->where(
            'date_to >= :required_date or date_to IS NULL'
        );

        $bind = ['store_id' => (int)$storeId, 'required_date' => $date];

        return $this->getConnection()->fetchRow($select, $bind);
    }
}
