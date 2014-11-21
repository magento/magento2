<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\DB;

use Magento\Framework\Api\CriteriaInterface;

/**
 * Class GenericMapper
 */
class GenericMapper extends AbstractMapper
{
    /**
     * Set initial conditions
     *
     * @return void
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
     */
    public function mapFields(array $fields)
    {
        $columns = $this->getSelect()->getPart(\Zend_Db_Select::COLUMNS);
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
        $this->getSelect()->setPart(\Zend_Db_Select::COLUMNS, $columns);
    }

    /**
     * Map limit
     *
     * @param int $offset
     * @param int $size
     * @return void
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
     */
    public function mapDistinct($flag)
    {
        $this->select->distinct($flag);
    }
}
