<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB;

use Magento\Framework\Api\CriteriaInterface;

/**
 * Class GenericMapper
 * @since 2.0.0
 */
class GenericMapper extends AbstractMapper
{
    /**
     * Set initial conditions
     *
     * @return void
     * @since 2.0.0
     */
    protected function init()
    {
        //
    }

    /**
     * Map criteria list
     *
     * @param \Magento\Framework\Api\CriteriaInterface[] $criteriaList
     * @return void
     * @since 2.0.0
     */
    public function mapCriteriaList(array $criteriaList)
    {
        foreach ($criteriaList as $criteria) {
            /** @var CriteriaInterface $criteria */
            $mapper = $criteria->getMapperInterfaceName();
            $mapperInstance = $this->mapperFactory->create($mapper, ['select' => $this->select]);
            $this->select = $mapperInstance->map($criteria);
        }
    }

    /**
     * Map filters
     *
     * @param array $filters
     * @return void
     * @since 2.0.0
     */
    public function mapFilters(array $filters)
    {
        $this->renderFiltersBefore();
        foreach ($filters as $filter) {
            switch ($filter['type']) {
                case 'or':
                    $condition = $this->getConnection()->quoteInto($filter['field'] . '=?', $filter['condition']);
                    $this->getSelect()->orWhere($condition);
                    break;
                case 'string':
                    $this->getSelect()->where($filter['condition']);
                    break;
                case 'public':
                    $field = $this->getMappedField($filter['field']);
                    $condition = $filter['condition'];
                    $this->getSelect()->where($this->getConditionSql($field, $condition), null, Select::TYPE_CONDITION);
                    break;
                default:
                    $condition = $this->getConnection()->quoteInto($filter['field'] . '=?', $filter['condition']);
                    $this->getSelect()->where($condition);
            }
        }
    }

    /**
     * Map order
     *
     * @param array $orders
     * @return void
     * @since 2.0.0
     */
    public function mapOrders(array $orders)
    {
        foreach ($orders as $field => $direction) {
            $this->select->order(new \Zend_Db_Expr($field . ' ' . $direction));
        }
    }

    /**
     * Map fields
     *
     * @param array $fields
     * @throws \Zend_Db_Select_Exception
     * @return void
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @since 2.0.0
     */
    public function mapFields(array $fields)
    {
        $columns = $this->getSelect()->getPart(\Magento\Framework\DB\Select::COLUMNS);
        $selectedUniqueNames = [];
        foreach ($fields as $fieldInfo) {
            if (is_string($fieldInfo)) {
                $fieldInfo = isset($this->map[$fieldInfo]) ? $this->map[$fieldInfo] : $fieldInfo;
            }
            list($correlationName, $field, $alias) = $fieldInfo;
            if (!is_string($alias)) {
                $alias = null;
            }
            if ($field instanceof \Zend_Db_Expr) {
                $field = $field->__toString();
            }
            $selectedUniqueName = $alias ?: $field;
            if (in_array($selectedUniqueName, $selectedUniqueNames)) {
                // ignore field since the alias is already used by another field
                continue;
            }
            $selectedUniqueNames[] = $selectedUniqueName;
            $columns[] = [$correlationName, $field, $alias];
        }
        $this->getSelect()->setPart(\Magento\Framework\DB\Select::COLUMNS, $columns);
    }

    /**
     * Map limit
     *
     * @param int $offset
     * @param int $size
     * @return void
     * @since 2.0.0
     */
    public function mapLimit($offset, $size)
    {
        $this->select->limitPage($offset, $size);
    }

    /**
     * Map distinct flag
     *
     * @param bool $flag
     * @return void
     * @since 2.0.0
     */
    public function mapDistinct($flag)
    {
        $this->select->distinct($flag);
    }
}
