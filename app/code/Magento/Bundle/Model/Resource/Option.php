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
namespace Magento\Bundle\Model\Resource;

/**
 * Bundle Option Resource Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Option extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Initialize connection and define resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalog_product_bundle_option', 'option_id');
    }

    /**
     * After save process
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        parent::_afterSave($object);

        $condition = array(
            'option_id = ?' => $object->getId(),
            'store_id = ? OR store_id = 0' => $object->getStoreId()
        );

        $write = $this->_getWriteAdapter();
        $write->delete($this->getTable('catalog_product_bundle_option_value'), $condition);

        $data = new \Magento\Framework\Object();
        $data->setOptionId($object->getId())->setStoreId($object->getStoreId())->setTitle($object->getTitle());

        $write->insert($this->getTable('catalog_product_bundle_option_value'), $data->getData());

        /**
         * also saving default value if this store view scope
         */

        if ($object->getStoreId()) {
            $data->setStoreId(0);
            $data->setTitle($object->getDefaultTitle());
            $write->insert($this->getTable('catalog_product_bundle_option_value'), $data->getData());
        }

        return $this;
    }

    /**
     * After delete process
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        parent::_afterDelete($object);

        $this->_getWriteAdapter()->delete(
            $this->getTable('catalog_product_bundle_option_value'),
            array('option_id = ?' => $object->getId())
        );

        return $this;
    }

    /**
     * Retrieve options searchable data
     *
     * @param int $productId
     * @param int $storeId
     * @return array
     */
    public function getSearchableData($productId, $storeId)
    {
        $adapter = $this->_getReadAdapter();

        $title = $adapter->getCheckSql(
            'option_title_store.title IS NOT NULL',
            'option_title_store.title',
            'option_title_default.title'
        );
        $bind = array('store_id' => $storeId, 'product_id' => $productId);
        $select = $adapter->select()->from(
            array('opt' => $this->getMainTable()),
            array()
        )->join(
            array('option_title_default' => $this->getTable('catalog_product_bundle_option_value')),
            'option_title_default.option_id = opt.option_id AND option_title_default.store_id = 0',
            array()
        )->joinLeft(
            array('option_title_store' => $this->getTable('catalog_product_bundle_option_value')),
            'option_title_store.option_id = opt.option_id AND option_title_store.store_id = :store_id',
            array('title' => $title)
        )->where(
            'opt.parent_id=:product_id'
        );
        if (!($searchData = $adapter->fetchCol($select, $bind))) {
            $searchData = array();
        }
        return $searchData;
    }
}
