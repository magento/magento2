<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Model\ResourceModel\Product;

class Link extends \Magento\Catalog\Model\ResourceModel\Product\Link
{
    const LINK_TYPE_GROUPED = 3;

    /**
     * Save grouped product relations
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $data
     *
     * @return \Magento\GroupedProduct\Model\ResourceModel\Product\Link
     */
    public function saveGroupedLinks($product, $data)
    {
        $connection = $this->getConnection();
        // check for change relations
        $bind = ['product_id' => (int)$product->getId(), 'link_type_id' => self::LINK_TYPE_GROUPED];
        $select = $connection->select()->from(
            $this->getMainTable(),
            ['linked_product_id']
        )->where(
            'product_id = :product_id'
        )->where(
            'link_type_id = :link_type_id'
        );
        $old = $connection->fetchCol($select, $bind);
        $new = array_keys($data);

        if (array_diff($old, $new) || array_diff($new, $old)) {
            $product->setIsRelationsChanged(true);
        }

        // save product links attributes
        $this->saveProductLinks($product, $data, self::LINK_TYPE_GROUPED);

        // Grouped product relations should be added to relation table
        $this->_catalogProductRelation->processRelations($product->getId(), $new);

        return $this;
    }

    /**
     * Retrieve Required children ids
     * Return grouped array, ex array(
     *   group => array(ids)
     * )
     *
     * @param int $parentId
     * @param int $typeId
     * @return array
     */
    public function getChildrenIds($parentId, $typeId)
    {
        $connection = $this->getConnection();
        $childrenIds = [];
        $bind = [':product_id' => (int)$parentId, ':link_type_id' => (int)$typeId];
        $select = $connection->select()->from(
            ['l' => $this->getMainTable()],
            ['linked_product_id']
        )->where(
            'product_id = :product_id'
        )->where(
            'link_type_id = :link_type_id'
        );

        $select->join(
            ['e' => $this->getTable('catalog_product_entity')],
            'e.entity_id = l.linked_product_id AND e.required_options = 0',
            []
        );

        $childrenIds[$typeId] = [];
        $result = $connection->fetchAll($select, $bind);
        foreach ($result as $row) {
            $childrenIds[$typeId][$row['linked_product_id']] = $row['linked_product_id'];
        }

        return $childrenIds;
    }
}
