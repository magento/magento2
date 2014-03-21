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
namespace Magento\GroupedProduct\Model\Resource\Product;

class Link extends \Magento\Catalog\Model\Resource\Product\Link
{
    const LINK_TYPE_GROUPED = 3;

    /**
     * Save grouped product relations
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $data
     *
     * @return \Magento\GroupedProduct\Model\Resource\Product\Link
     */
    public function saveGroupedLinks($product, $data)
    {
        $adapter = $this->_getWriteAdapter();
        // check for change relations
        $bind = array('product_id' => (int)$product->getId(), 'link_type_id' => self::LINK_TYPE_GROUPED);
        $select = $adapter->select()->from(
            $this->getMainTable(),
            array('linked_product_id')
        )->where(
            'product_id = :product_id'
        )->where(
            'link_type_id = :link_type_id'
        );
        $old = $adapter->fetchCol($select, $bind);
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
        $adapter = $this->_getReadAdapter();
        $childrenIds = array();
        $bind = array(':product_id' => (int)$parentId, ':link_type_id' => (int)$typeId);
        $select = $adapter->select()->from(
            array('l' => $this->getMainTable()),
            array('linked_product_id')
        )->where(
            'product_id = :product_id'
        )->where(
            'link_type_id = :link_type_id'
        );

        $select->join(
            array('e' => $this->getTable('catalog_product_entity')),
            'e.entity_id = l.linked_product_id AND e.required_options = 0',
            array()
        );

        $childrenIds[$typeId] = array();
        $result = $adapter->fetchAll($select, $bind);
        foreach ($result as $row) {
            $childrenIds[$typeId][$row['linked_product_id']] = $row['linked_product_id'];
        }

        return $childrenIds;
    }
}
