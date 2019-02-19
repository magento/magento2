<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Model\ResourceModel\Widget;

use Magento\Framework\Model\AbstractModel;

/**
 * Widget Instance Resource Model
 *
 * @api
 * @since 100.0.2
 */
class Instance extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('widget_instance', 'instance_id');
    }

    /**
     * Perform actions after object load
     *
     * @param \Magento\Widget\Model\Widget\Instance $object
     * @return $this
     */
    protected function _afterLoad(AbstractModel $object)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getTable('widget_instance_page')
        )->where(
            'instance_id = ?',
            (int)$object->getId()
        );
        $result = $connection->fetchAll($select);
        $object->setData('page_groups', $result);
        return parent::_afterLoad($object);
    }

    /**
     * Perform actions after object save
     *
     * @param \Magento\Widget\Model\Widget\Instance $object
     * @return $this
     */
    protected function _afterSave(AbstractModel $object)
    {
        $pageTable = $this->getTable('widget_instance_page');
        $pageLayoutTable = $this->getTable('widget_instance_page_layout');
        $connection = $this->getConnection();

        $select = $connection->select()->from($pageTable, 'page_id')->where('instance_id = ?', (int)$object->getId());
        $pageIds = $connection->fetchCol($select);

        $removePageIds = array_diff($pageIds, $object->getData('page_group_ids'));

        if (is_array($pageIds) && count($pageIds) > 0) {
            $inCond = $connection->prepareSqlCondition('page_id', ['in' => $pageIds]);

            $select = $connection->select()->from($pageLayoutTable, 'layout_update_id')->where($inCond);
            $removeLayoutUpdateIds = $connection->fetchCol($select);

            $connection->delete($pageLayoutTable, $inCond);
            $this->_deleteLayoutUpdates($removeLayoutUpdateIds);
        }

        $this->_deleteWidgetInstancePages($removePageIds);

        foreach ($object->getData('page_groups') as $pageGroup) {
            $pageLayoutUpdateIds = $this->_saveLayoutUpdates($object, $pageGroup);
            $data = [
                'page_group' => $pageGroup['group'],
                'layout_handle' => $pageGroup['layout_handle'],
                'block_reference' => $pageGroup['block_reference'],
                'page_for' => $pageGroup['for'],
                'entities' => $pageGroup['entities'],
                'page_template' => $pageGroup['template'],
            ];
            $pageId = $pageGroup['page_id'];
            if (in_array($pageGroup['page_id'], $pageIds)) {
                $connection->update($pageTable, $data, ['page_id = ?' => (int)$pageId]);
            } else {
                $connection->insert($pageTable, array_merge(['instance_id' => $object->getId()], $data));
                $pageId = $connection->lastInsertId($pageTable);
            }
            foreach ($pageLayoutUpdateIds as $layoutUpdateId) {
                $connection->insert(
                    $pageLayoutTable,
                    ['page_id' => $pageId, 'layout_update_id' => $layoutUpdateId]
                );
            }
        }

        return parent::_afterSave($object);
    }

    /**
     * Prepare and save layout updates data
     *
     * @param \Magento\Widget\Model\Widget\Instance $widgetInstance
     * @param array $pageGroupData
     * @return string[] of inserted layout updates ids
     */
    protected function _saveLayoutUpdates($widgetInstance, $pageGroupData)
    {
        $connection = $this->getConnection();
        $pageLayoutUpdateIds = [];
        $storeIds = $this->_prepareStoreIds($widgetInstance->getStoreIds());
        $layoutUpdateTable = $this->getTable('layout_update');
        $layoutUpdateLinkTable = $this->getTable('layout_link');

        foreach ($pageGroupData['layout_handle_updates'] as $handle) {
            $xml = $widgetInstance->generateLayoutUpdateXml(
                $pageGroupData['block_reference'],
                $pageGroupData['template']
            );
            $insert = ['handle' => $handle, 'xml' => $xml];
            if (strlen($widgetInstance->getSortOrder())) {
                $insert['sort_order'] = $widgetInstance->getSortOrder();
            }

            $connection->insert($layoutUpdateTable, $insert);
            $layoutUpdateId = $connection->lastInsertId($layoutUpdateTable);
            $pageLayoutUpdateIds[] = $layoutUpdateId;

            $data = [];
            foreach ($storeIds as $storeId) {
                $data[] = [
                    'store_id' => $storeId,
                    'theme_id' => $widgetInstance->getThemeId(),
                    'layout_update_id' => $layoutUpdateId,
                ];
            }
            $connection->insertMultiple($layoutUpdateLinkTable, $data);
        }
        return $pageLayoutUpdateIds;
    }

    /**
     * Prepare store ids.
     * If one of store id is default (0) return all store ids
     *
     * @param array $storeIds
     * @return array
     */
    protected function _prepareStoreIds($storeIds)
    {
        if (in_array('0', $storeIds)) {
            $storeIds = [0];
        }
        return $storeIds;
    }

    /**
     * Perform actions before object delete.
     * Collect page ids and layout update ids and set to object for further delete
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _beforeDelete(AbstractModel $object)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            ['main_table' => $this->getTable('widget_instance_page')],
            []
        )->joinInner(
            ['layout_page_table' => $this->getTable('widget_instance_page_layout')],
            'layout_page_table.page_id = main_table.page_id',
            ['layout_update_id']
        )->where(
            'main_table.instance_id=?',
            $object->getId()
        );
        $result = $connection->fetchCol($select);
        $object->setLayoutUpdateIdsToDelete($result);
        return $this;
    }

    /**
     * Perform actions after object delete.
     * Delete layout updates by layout update ids collected in _beforeSave
     *
     * @param \Magento\Widget\Model\Widget\Instance $object
     * @return $this
     */
    protected function _afterDelete(AbstractModel $object)
    {
        $this->_deleteLayoutUpdates($object->getLayoutUpdateIdsToDelete());
        return parent::_afterDelete($object);
    }

    /**
     * Delete widget instance pages by given ids
     *
     * @param array $pageIds
     * @return $this
     */
    protected function _deleteWidgetInstancePages($pageIds)
    {
        $connection = $this->getConnection();
        if ($pageIds) {
            $inCond = $connection->prepareSqlCondition('page_id', ['in' => $pageIds]);
            $connection->delete($this->getTable('widget_instance_page'), $inCond);
        }
        return $this;
    }

    /**
     * Delete layout updates by given ids
     *
     * @param array $layoutUpdateIds
     * @return $this
     */
    protected function _deleteLayoutUpdates($layoutUpdateIds)
    {
        $connection = $this->getConnection();
        if ($layoutUpdateIds) {
            $inCond = $connection->prepareSqlCondition('layout_update_id', ['in' => $layoutUpdateIds]);
            $connection->delete($this->getTable('layout_update'), $inCond);
        }
        return $this;
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $id
     * @return string[]
     */
    public function lookupStoreIds($id)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getMainTable(),
            'store_ids'
        )->where(
            "{$this->getIdFieldName()} = ?",
            (int)$id
        );
        $storeIds = $connection->fetchOne($select);
        return $storeIds ? explode(',', $storeIds) : [];
    }
}
