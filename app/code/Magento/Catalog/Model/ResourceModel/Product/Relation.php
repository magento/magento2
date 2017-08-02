<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Product;

/**
 * Catalog Product Relations Resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Relation extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model and define main table
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init('catalog_product_relation', 'parent_id');
    }

    /**
     * Save (rebuild) product relations
     *
     * @param int $parentId
     * @param array $childIds
     * @return $this
     * @since 2.0.0
     */
    public function processRelations($parentId, $childIds)
    {
        $select = $this->getConnection()->select()->from(
            $this->getMainTable(),
            ['child_id']
        )->where(
            'parent_id = ?',
            $parentId
        );
        $old = $this->getConnection()->fetchCol($select);
        $new = $childIds;

        $insert = array_diff($new, $old);
        $delete = array_diff($old, $new);

        $this->addRelations($parentId, $insert);
        $this->removeRelations($parentId, $delete);

        return $this;
    }

    /**
     * Add Relation on duplicate update
     *
     * @param int $parentId
     * @param int $childId
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.1.0
     */
    public function addRelation($parentId, $childId)
    {
        $this->getConnection()->insertOnDuplicate(
            $this->getMainTable(),
            ['parent_id' => $parentId, 'child_id' => $childId]
        );
        return $this;
    }

    /**
     * Add Relations
     *
     * @param int $parentId
     * @param int[] $childIds
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function addRelations($parentId, $childIds)
    {
        if (!empty($childIds)) {
            $insertData = [];
            foreach ($childIds as $childId) {
                $insertData[] = ['parent_id' => $parentId, 'child_id' => $childId];
            }
            $this->getConnection()->insertMultiple($this->getMainTable(), $insertData);
        }
        return $this;
    }

    /**
     * Remove Relations
     *
     * @param int $parentId
     * @param int[] $childIds
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function removeRelations($parentId, $childIds)
    {
        if (!empty($childIds)) {
            $where = join(
                ' AND ',
                [
                    $this->getConnection()->quoteInto('parent_id = ?', $parentId),
                    $this->getConnection()->quoteInto('child_id IN(?)', $childIds)
                ]
            );
            $this->getConnection()->delete($this->getMainTable(), $where);
        }
        return $this;
    }
}
