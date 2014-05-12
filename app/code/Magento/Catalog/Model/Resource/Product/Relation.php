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
            array('child_id')
        )->where(
            'parent_id = ?',
            $parentId
        );
        $old = $this->_getReadAdapter()->fetchCol($select);
        $new = $childIds;

        $insert = array_diff($new, $old);
        $delete = array_diff($old, $new);

        if (!empty($insert)) {
            $insertData = array();
            foreach ($insert as $childId) {
                $insertData[] = array('parent_id' => $parentId, 'child_id' => $childId);
            }
            $this->_getWriteAdapter()->insertMultiple($this->getMainTable(), $insertData);
        }
        if (!empty($delete)) {
            $where = join(
                ' AND ',
                array(
                    $this->_getWriteAdapter()->quoteInto('parent_id = ?', $parentId),
                    $this->_getWriteAdapter()->quoteInto('child_id IN(?)', $delete)
                )
            );
            $this->_getWriteAdapter()->delete($this->getMainTable(), $where);
        }

        return $this;
    }
}
