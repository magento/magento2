<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Abstract Rule entity resource model
 *
 * @author Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Rule\Model\Resource;

abstract class AbstractResource extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Store associated with rule entities information map
     *
     * Example:
     * array(
     *    'entity_type1' => array(
     *        'associations_table' => 'table_name',
     *        'rule_id_field'      => 'rule_id',
     *        'entity_id_field'    => 'entity_id'
     *    ),
     *    'entity_type2' => array(
     *        'associations_table' => 'table_name',
     *        'rule_id_field'      => 'rule_id',
     *        'entity_id_field'    => 'entity_id'
     *    )
     *    ....
     * )
     *
     * @var array
     */
    protected $_associatedEntitiesMap = [];

    /**
     * Prepare rule's active "from" and "to" dates
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    public function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $fromDate = $object->getFromDate();
        if ($fromDate instanceof \Zend_Date) {
            $object->setFromDate($fromDate->toString(\Magento\Framework\Stdlib\DateTime::DATETIME_INTERNAL_FORMAT));
        } elseif (!is_string($fromDate) || empty($fromDate)) {
            $object->setFromDate(null);
        }

        $toDate = $object->getToDate();
        if ($toDate instanceof \Zend_Date) {
            $object->setToDate($toDate->toString(\Magento\Framework\Stdlib\DateTime::DATETIME_INTERNAL_FORMAT));
        } elseif (!is_string($toDate) || empty($toDate)) {
            $object->setToDate(null);
        }

        parent::_beforeSave($object);
        return $this;
    }

    /**
     * Bind specified rules to entities
     *
     * @param int[]|int|string $ruleIds
     * @param int[]|int|string $entityIds
     * @param string $entityType
     * @return $this
     * @throws \Exception
     */
    public function bindRuleToEntity($ruleIds, $entityIds, $entityType)
    {
        $this->_getWriteAdapter()->beginTransaction();

        try {
            $this->_multiplyBunchInsert($ruleIds, $entityIds, $entityType);
        } catch (\Exception $e) {
            $this->_getWriteAdapter()->rollback();
            throw $e;
        }

        $this->_getWriteAdapter()->commit();

        return $this;
    }

    /**
     * Multiply rule ids by entity ids and insert
     *
     * @param int|[] $ruleIds
     * @param int|[] $entityIds
     * @param string $entityType
     * @return $this
     */
    protected function _multiplyBunchInsert($ruleIds, $entityIds, $entityType)
    {
        if (empty($ruleIds) || empty($entityIds)) {
            return $this;
        }
        if (!is_array($ruleIds)) {
            $ruleIds = [(int)$ruleIds];
        }
        if (!is_array($entityIds)) {
            $entityIds = [(int)$entityIds];
        }
        $data = [];
        $count = 0;
        $entityInfo = $this->_getAssociatedEntityInfo($entityType);
        foreach ($ruleIds as $ruleId) {
            foreach ($entityIds as $entityId) {
                $data[] = [
                    $entityInfo['entity_id_field'] => $entityId,
                    $entityInfo['rule_id_field'] => $ruleId,
                ];
                $count++;
                if ($count % 1000 == 0) {
                    $this->_getWriteAdapter()->insertOnDuplicate(
                        $this->getTable($entityInfo['associations_table']),
                        $data,
                        [$entityInfo['rule_id_field']]
                    );
                    $data = [];
                }
            }
        }
        if (!empty($data)) {
            $this->_getWriteAdapter()->insertOnDuplicate(
                $this->getTable($entityInfo['associations_table']),
                $data,
                [$entityInfo['rule_id_field']]
            );
        }

        $this->_getWriteAdapter()->delete(
            $this->getTable($entityInfo['associations_table']),
            $this->_getWriteAdapter()->quoteInto(
                $entityInfo['rule_id_field'] . ' IN (?) AND ',
                $ruleIds
            ) . $this->_getWriteAdapter()->quoteInto(
                $entityInfo['entity_id_field'] . ' NOT IN (?)',
                $entityIds
            )
        );
        return $this;
    }

    /**
     * Unbind specified rules from entities
     *
     * @param int[]|int|string $ruleIds
     * @param int[]|int|string $entityIds
     * @param string $entityType
     * @return $this
     */
    public function unbindRuleFromEntity($ruleIds, $entityIds, $entityType)
    {
        $writeAdapter = $this->_getWriteAdapter();
        $entityInfo = $this->_getAssociatedEntityInfo($entityType);

        if (!is_array($entityIds)) {
            $entityIds = [(int)$entityIds];
        }
        if (!is_array($ruleIds)) {
            $ruleIds = [(int)$ruleIds];
        }

        $where = [];
        if (!empty($ruleIds)) {
            $where[] = $writeAdapter->quoteInto($entityInfo['rule_id_field'] . ' IN (?)', $ruleIds);
        }
        if (!empty($entityIds)) {
            $where[] = $writeAdapter->quoteInto($entityInfo['entity_id_field'] . ' IN (?)', $entityIds);
        }

        $writeAdapter->delete($this->getTable($entityInfo['associations_table']), implode(' AND ', $where));

        return $this;
    }

    /**
     * Retrieve rule's associated entity Ids by entity type
     *
     * @param int $ruleId
     * @param string $entityType
     * @return array
     */
    public function getAssociatedEntityIds($ruleId, $entityType)
    {
        $entityInfo = $this->_getAssociatedEntityInfo($entityType);

        $select = $this->_getReadAdapter()->select()->from(
            $this->getTable($entityInfo['associations_table']),
            [$entityInfo['entity_id_field']]
        )->where(
            $entityInfo['rule_id_field'] . ' = ?',
            $ruleId
        );

        return $this->_getReadAdapter()->fetchCol($select);
    }

    /**
     * Retrieve website ids of specified rule
     *
     * @param int $ruleId
     * @return array
     */
    public function getWebsiteIds($ruleId)
    {
        return $this->getAssociatedEntityIds($ruleId, 'website');
    }

    /**
     * Retrieve customer group ids of specified rule
     *
     * @param int $ruleId
     * @return array
     */
    public function getCustomerGroupIds($ruleId)
    {
        return $this->getAssociatedEntityIds($ruleId, 'customer_group');
    }

    /**
     * Retrieve correspondent entity information (associations table name, columns names)
     * of rule's associated entity by specified entity type
     *
     * @param string $entityType
     * @return array
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _getAssociatedEntityInfo($entityType)
    {
        if (isset($this->_associatedEntitiesMap[$entityType])) {
            return $this->_associatedEntitiesMap[$entityType];
        }

        throw new \Magento\Framework\Model\Exception(
            __('There is no information about associated entity type "%1".', $entityType),
            0
        );
    }
}
