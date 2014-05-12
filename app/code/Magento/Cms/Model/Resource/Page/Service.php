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
namespace Magento\Cms\Model\Resource\Page;

/**
 * Cms page service resource model
 */
class Service extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Init cms page service model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('cms_page', 'page_id');
    }

    /**
     * Unlinks from $fromStoreId store pages that have same identifiers as pages in $byStoreId
     *
     * Routine is intented to be used before linking pages of some store ($byStoreId) to other store ($fromStoreId)
     * to prevent duplication of url keys
     *
     * Resolved $byLinkTable can be provided when restoring links from some backup table
     *
     * @param int $fromStoreId
     * @param int $byStoreId
     * @param string $byLinkTable
     *
     * @return $this
     */
    public function unlinkConflicts($fromStoreId, $byStoreId, $byLinkTable = null)
    {
        $readAdapter = $this->_getReadAdapter();

        $linkTable = $this->getTable('cms_page_store');
        $mainTable = $this->getMainTable();
        $byLinkTable = $byLinkTable ? $byLinkTable : $linkTable;

        // Select all page ids of $fromStoreId that have identifiers as some pages in $byStoreId
        $select = $readAdapter->select()->from(
            array('from_link' => $linkTable),
            'page_id'
        )->join(
            array('from_entity' => $mainTable),
            $readAdapter->quoteInto(
                'from_entity.page_id = from_link.page_id AND from_link.store_id = ?',
                $fromStoreId
            ),
            array()
        )->join(
            array('by_entity' => $mainTable),
            'from_entity.identifier = by_entity.identifier AND from_entity.page_id != by_entity.page_id',
            array()
        )->join(
            array('by_link' => $byLinkTable),
            $readAdapter->quoteInto('by_link.page_id = by_entity.page_id AND by_link.store_id = ?', $byStoreId),
            array()
        );
        $pageIds = $readAdapter->fetchCol($select);

        // Unlink found pages
        if ($pageIds) {
            $writeAdapter = $this->_getWriteAdapter();
            $where = array('page_id IN (?)' => $pageIds, 'AND store_id = ?' => $fromStoreId);
            $writeAdapter->delete($linkTable, $where);
        }
        return $this;
    }
}
