<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Resource\Product;

/**
 * Catalog Product Relations Resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Relation extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Initialize resource model and define main table
     *
     * @return void
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
     */
    public function processRelations($parentId, $childIds)
    {
        $select = $this->_getReadAdapter()->select()->from(
            $this->getMainTable(),
            ['child_id']
        )->where(
            'parent_id = ?',
            $parentId
        );
        $old = $this->_getReadAdapter()->fetchCol($select);
        $new = $childIds;

        $insert = array_diff($new, $old);
        $delete = array_diff($old, $new);

        if (!empty($insert)) {
            $insertData = [];
            foreach ($insert as $childId) {
                $insertData[] = ['parent_id' => $parentId, 'child_id' => $childId];
            }
            $this->_getWriteAdapter()->insertMultiple($this->getMainTable(), $insertData);
        }
        if (!empty($delete)) {
            $where = join(
                ' AND ',
                [
                    $this->_getWriteAdapter()->quoteInto('parent_id = ?', $parentId),
                    $this->_getWriteAdapter()->quoteInto('child_id IN(?)', $delete)
                ]
            );
            $this->_getWriteAdapter()->delete($this->getMainTable(), $where);
        }

        return $this;
    }
}
