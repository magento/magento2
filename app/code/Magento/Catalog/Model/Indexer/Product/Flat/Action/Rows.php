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
 * @category    Magento
 * @package     Magento_Catalog
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Model\Indexer\Product\Flat\Action;
/**
 * Class Rows reindex action for mass actions
 *
 * @package Magento\Catalog\Model\Indexer\Product\Flat\Action
 */
class Rows extends \Magento\Catalog\Model\Indexer\Product\Flat\AbstractAction
{
    /**
     * Execute multiple rows reindex action
     *
     * @param array $ids
     *
     * @return \Magento\Catalog\Model\Indexer\Product\Flat\Action\Rows
     * @throws \Magento\Core\Exception
     */
    public function execute($ids)
    {
        if (empty($ids)) {
            throw new \Magento\Core\Exception(__('Bad value was supplied.'));
        }
        foreach ($this->_storeManager->getStores() as $store) {
            $idsBatches = array_chunk($ids, \Magento\Catalog\Helper\Product\Flat\Indexer::BATCH_SIZE);
            foreach ($idsBatches as $changedIds) {
                $this->_removeDeletedProducts($changedIds, $store->getId());
                if (!empty($changedIds)) {
                    $this->_reindex($store->getId(), $changedIds);
                }
            }
        }
        return $this;
    }

    /**
     * Move data from temporary flat table into regular flat table.
     *
     * @return \Magento\Catalog\Model\Indexer\Product\Flat\Action\Rows
     */
    protected function _moveDataToFlatTable()
    {
        $flatTable = $this->_productIndexerHelper->getFlatTableName($this->_storeId);

        if (!$this->_connection->isTableExists($flatTable)) {
            parent::_moveDataToFlatTable();
        } else {
            $describe = $this->_connection->describeTable(
                $this->_productIndexerHelper->getFlatTableName($this->_storeId)
            );
            $columns  = $this->_productIndexerHelper->getFlatColumns();
            $columns  = array_keys(array_intersect_key($describe, $columns));
            $select   = $this->_connection->select();

            $select->from(
                array(
                    'tf' => $this->_getTemporaryTableName(
                            $this->_productIndexerHelper->getFlatTableName($this->_storeId)
                        ),
                ),
                $columns
            );
            $sql = $select->insertFromSelect($flatTable, $columns);
            $this->_connection->query($sql);

            $this->_connection->dropTable(
                $this->_getTemporaryTableName($this->_productIndexerHelper->getFlatTableName($this->_storeId))
            );
        }

        return $this;
    }
}
