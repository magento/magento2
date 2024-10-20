<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\ResourceModel\Entity\Attribute;

/**
 * Entity attribute option resource model
 */
class Option extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     * @codeCoverageIgnore
     */
    protected function _construct()
    {
        $this->_init('eav_attribute_option', 'option_id');
    }

    /**
     * Add Join with option value for collection select
     *
     * @param \Magento\Eav\Model\Entity\Collection\AbstractCollection $collection
     * @param \Magento\Eav\Model\Entity\Attribute $attribute
     * @param \Zend_Db_Expr $valueExpr
     * @return $this
     */
    public function addOptionValueToCollection($collection, $attribute, $valueExpr)
    {
        $connection = $this->getConnection();
        $attributeCode = $attribute->getAttributeCode();
        $optionTable1 = $attributeCode . '_option_value_t1';
        $optionTable2 = $attributeCode . '_option_value_t2';
        $tableJoinCond1 = "{$optionTable1}.option_id={$valueExpr} AND {$optionTable1}.store_id=0";
        $tableJoinCond2 = $connection->quoteInto(
            "{$optionTable2}.option_id={$valueExpr} AND {$optionTable2}.store_id=?",
            $collection->getStoreId()
        );
        $valueIdExpr = $connection->getIfNullSql(
            "{$optionTable2}.option_id",
            "{$optionTable1}.option_id"
        );
        $valueExpr = $connection->getIfNullSql(
            "{$optionTable2}.value",
            "{$optionTable1}.value"
        );

        $collection->getSelect()->joinLeft(
            [$optionTable1 => $this->getTable('eav_attribute_option_value')],
            $tableJoinCond1,
            []
        )->joinLeft(
            [$optionTable2 => $this->getTable('eav_attribute_option_value')],
            $tableJoinCond2,
            [
                $attributeCode => $valueIdExpr,
                $attributeCode . '_value' => $valueExpr,
            ]
        );

        return $this;
    }

    /**
     * Add Join with option for collection select
     *
     * @param \Magento\Eav\Model\Entity\Collection\AbstractCollection $collection
     * @param \Magento\Eav\Model\Entity\Attribute $attribute
     * @param \Zend_Db_Expr $valueExpr
     * @return $this
     */
    public function addOptionToCollection($collection, $attribute, $valueExpr)
    {
        $connection = $this->getConnection();
        $attributeCode = $attribute->getAttributeCode();
        $optionTable1 = $attributeCode . '_option_t1';
        $tableJoinCond1 = "{$optionTable1}.option_id={$valueExpr}";
        $valueExpr = $connection->getIfNullSql(
            "{$optionTable1}.sort_order"
        );

        $collection->getSelect()->joinLeft(
            [$optionTable1 => $this->getTable('eav_attribute_option')],
            $tableJoinCond1,
            ["{$attributeCode}_order" => $valueExpr]
        );

        return $this;
    }

    /**
     * Retrieve Select for update Flat data
     *
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute
     * @param int $store
     * @param bool $hasValueField flag which require option value
     * @return \Magento\Framework\DB\Select
     */
    public function getFlatUpdateSelect(
        \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute,
        $store,
        $hasValueField = true
    ) {
        $connection = $this->getConnection();
        $attributeTable = $attribute->getBackend()->getTable();
        $attributeCode = $attribute->getAttributeCode();

        $joinConditionTemplate = "%s.entity_id = %s.entity_id" .
            " AND %s.entity_type_id = " .
            $attribute->getEntityTypeId() .
            " AND %s.attribute_id = " .
            $attribute->getId() .
            " AND %s.store_id = %d";
        $joinCondition = sprintf(
            $joinConditionTemplate,
            'e',
            't1',
            't1',
            't1',
            't1',
            \Magento\Store\Model\Store::DEFAULT_STORE_ID
        );
        if ($attribute->getFlatAddChildData()) {
            $joinCondition .= ' AND e.child_id = t1.entity_id';
        }

        $valueExpr = $connection->getCheckSql('t2.value_id > 0', 't2.value', 't1.value');
        /** @var $select \Magento\Framework\DB\Select */
        $select = $connection->select()->joinLeft(
            ['t1' => $attributeTable],
            $joinCondition,
            []
        )->joinLeft(
            ['t2' => $attributeTable],
            sprintf($joinConditionTemplate, 't1', 't2', 't2', 't2', 't2', $store),
            [$attributeCode => $valueExpr]
        );

        if ($attribute->getFrontend()->getInputType() != 'multiselect' && $hasValueField) {
            $valueIdExpr = $connection->getCheckSql('to2.value_id > 0', 'to2.value', 'to1.value');
            $select->joinLeft(
                ['to1' => $this->getTable('eav_attribute_option_value')],
                "to1.option_id = {$valueExpr} AND to1.store_id = 0",
                []
            )->joinLeft(
                ['to2' => $this->getTable('eav_attribute_option_value')],
                $connection->quoteInto("to2.option_id = {$valueExpr} AND to2.store_id = ?", $store),
                [$attributeCode . '_value' => $valueIdExpr]
            );
        }

        if ($attribute->getFlatAddChildData()) {
            $select->where('e.is_child = 0');
        }

        return $select;
    }
}
