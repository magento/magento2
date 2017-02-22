<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model\ResourceModel;

/**
 * Bundle Selection Resource Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Selection extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table and id field
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalog_product_bundle_selection', 'selection_id');
    }

    /**
     * Retrieve Required children ids
     * Return grouped array, ex array(
     *   group => array(ids)
     * )
     *
     * @param int $parentId
     * @param bool $required
     * @return array
     */
    public function getChildrenIds($parentId, $required = true)
    {
        $childrenIds = [];
        $notRequired = [];
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            ['tbl_selection' => $this->getMainTable()],
            ['product_id', 'parent_product_id', 'option_id']
        )->join(
            ['e' => $this->getTable('catalog_product_entity')],
            'e.entity_id = tbl_selection.product_id AND e.required_options=0',
            []
        )->join(
            ['tbl_option' => $this->getTable('catalog_product_bundle_option')],
            'tbl_option.option_id = tbl_selection.option_id',
            ['required']
        )->where(
            'tbl_selection.parent_product_id = :parent_id'
        );
        foreach ($connection->fetchAll($select, ['parent_id' => $parentId]) as $row) {
            if ($row['required']) {
                $childrenIds[$row['option_id']][$row['product_id']] = $row['product_id'];
            } else {
                $notRequired[$row['option_id']][$row['product_id']] = $row['product_id'];
            }
        }

        if (!$required) {
            $childrenIds = array_merge($childrenIds, $notRequired);
        } else {
            if (!$childrenIds) {
                foreach ($notRequired as $groupedChildrenIds) {
                    foreach ($groupedChildrenIds as $childId) {
                        $childrenIds[0][$childId] = $childId;
                    }
                }
            }
            if (!$childrenIds) {
                $childrenIds = [[]];
            }
        }

        return $childrenIds;
    }

    /**
     * Retrieve array of related bundle product ids by selection product id(s)
     *
     * @param int|array $childId
     * @return array
     */
    public function getParentIdsByChild($childId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->distinct(
            true
        )->from(
            $this->getMainTable(),
            'parent_product_id'
        )->where(
            'product_id IN(?)',
            $childId
        );

        return $connection->fetchCol($select);
    }

    /**
     * Save bundle item price per website
     *
     * @param \Magento\Bundle\Model\Selection $item
     * @return void
     */
    public function saveSelectionPrice($item)
    {
        $connection = $this->getConnection();
        if ($item->getDefaultPriceScope()) {
            $connection->delete(
                $this->getTable('catalog_product_bundle_selection_price'),
                ['selection_id = ?' => $item->getSelectionId(), 'website_id = ?' => $item->getWebsiteId()]
            );
        } else {
            $values = [
                'selection_id' => $item->getSelectionId(),
                'website_id' => $item->getWebsiteId(),
                'selection_price_type' => $item->getSelectionPriceType(),
                'selection_price_value' => $item->getSelectionPriceValue(),
            ];
            $connection->insertOnDuplicate(
                $this->getTable('catalog_product_bundle_selection_price'),
                $values,
                ['selection_price_type', 'selection_price_value']
            );
        }
    }
}
