<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Rule\Model\ResourceModel;

/**
 * Abstract Rule entity resource model
 *
 * @api
 * @since 100.0.2
 */
abstract class AbstractResource extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
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
        $this->resolveDate($object, 'from_date');
        $this->resolveDate($object, 'to_date');
        parent::_beforeSave($object);
        return $this;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param string $dateIdentifier
     * @return void
     */
    private function resolveDate(\Magento\Framework\Model\AbstractModel $object, $dateIdentifier)
    {
        $date = $object->getData($dateIdentifier);
        if ($date instanceof \DateTimeInterface) {
            $object->setData($dateIdentifier, $date->format('Y-m-d H:i:s'));
        } elseif (!is_string($date) || empty($date)) {
            $object->setData($dateIdentifier, null);
        }
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
        $this->getConnection()->beginTransaction();

        try {
            $this->_multiplyBunchInsert($ruleIds, $entityIds, $entityType);
        } catch (\Exception $e) {
            $this->getConnection()->rollback();
            throw $e;
        }

        $this->getConnection()->commit();

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
                    $this->getConnection()->insertOnDuplicate(
                        $this->getTable($entityInfo['associations_table']),
                        $data,
                        [$entityInfo['rule_id_field']]
                    );
                    $data = [];
                }
            }
        }
        if (!empty($data)) {
            $this->getConnection()->insertOnDuplicate(
                $this->getTable($entityInfo['associations_table']),
                $data,
                [$entityInfo['rule_id_field']]
            );
        }

        $this->getConnection()->delete(
            $this->getTable($entityInfo['associations_table']),
            $this->getConnection()->quoteInto(
                $entityInfo['rule_id_field'] . ' IN (?) AND ',
                $ruleIds
            ) . $this->getConnection()->quoteInto(
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
        $connection = $this->getConnection();
        $entityInfo = $this->_getAssociatedEntityInfo($entityType);

        if (!is_array($entityIds)) {
            $entityIds = [(int)$entityIds];
        }
        if (!is_array($ruleIds)) {
            $ruleIds = [(int)$ruleIds];
        }

        $where = [];
        if (!empty($ruleIds)) {
            $where[] = $connection->quoteInto($entityInfo['rule_id_field'] . ' IN (?)', $ruleIds);
        }
        if (!empty($entityIds)) {
            $where[] = $connection->quoteInto($entityInfo['entity_id_field'] . ' IN (?)', $entityIds);
        }

        $connection->delete($this->getTable($entityInfo['associations_table']), implode(' AND ', $where));

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

        $select = $this->getConnection()->select()->from(
            $this->getTable($entityInfo['associations_table']),
            [$entityInfo['entity_id_field']]
        )->where(
            $entityInfo['rule_id_field'] . ' = ?',
            $ruleId
        );

        return $this->getConnection()->fetchCol($select);
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
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getAssociatedEntityInfo($entityType)
    {
        if (isset($this->_associatedEntitiesMap[$entityType])) {
            return $this->_associatedEntitiesMap[$entityType];
        }

        throw new \Magento\Framework\Exception\LocalizedException(
            __('There is no information about associated entity type "%1".', $entityType)
        );
    }
}
