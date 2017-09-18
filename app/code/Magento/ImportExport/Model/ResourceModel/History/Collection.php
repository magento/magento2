<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Model\ResourceModel\History;

use \Magento\ImportExport\Model\History;

/**
 * Import history collection
 *
 * @api
 * @since 100.0.2
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Link table name
     *
     * @var string
     */
    protected $_linkTable;

    /**
     * Define resource model and assign link table name
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\ImportExport\Model\History::class,
            \Magento\ImportExport\Model\ResourceModel\History::class
        );
        $this->_linkTable = $this->getTable('admin_user');
    }

    /**
     * Init select
     *
     * @return \Magento\ImportExport\Model\ResourceModel\History\Collection
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()->joinLeft(
            ['link_table' => $this->_linkTable],
            'link_table.user_id = main_table.user_id',
            ['username']
        )->where(
            'execution_time != ? OR (error_file != "" AND execution_time = ?)',
            History::IMPORT_VALIDATION,
            History::IMPORT_VALIDATION
        );

        return $this;
    }
}
