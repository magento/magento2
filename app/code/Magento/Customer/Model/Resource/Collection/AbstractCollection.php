<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Customer\Model\Resource\Collection;

class AbstractCollection extends \Magento\Eav\Model\Entity\Collection\AbstractCollection
{
    /**
     * {@inheritdoc}
     *
     * Redeclare method to disable entity_type_id filter
     */
    protected function _initSelect()
    {
        $this->getSelect()->from(['e' => $this->getEntity()->getEntityTable()]);
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * Redeclare method to disable entity_type_id filter
     */
    protected function _getLoadAttributesSelect($table, $attributeIds = [])
    {
        if (empty($attributeIds)) {
            $attributeIds = $this->_selectAttributes;
        }
        $entityIdField = $this->getEntity()->getEntityIdField();
        $select = $this->getConnection()->select()->from(
            $table,
            [$entityIdField, 'attribute_id']

        )->where(
            "{$entityIdField} IN (?)",
            array_keys($this->_itemsById)
        )->where(
            'attribute_id IN (?)',
            $attributeIds
        );
        return $select;
    }
}
