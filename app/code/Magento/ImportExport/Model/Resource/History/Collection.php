<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Model\Resource\History;

use \Magento\ImportExport\Model\History;

/**
 * Import history collection
 */
class Collection extends \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
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
        $this->_init('Magento\ImportExport\Model\History', 'Magento\ImportExport\Model\Resource\History');
        $this->_linkTable = $this->getTable('admin_user');
    }

    /**
     * Init select
     *
     * @return \Magento\ImportExport\Model\Resource\History\Collection
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()->joinLeft(
            ['link_table' => $this->_linkTable],
            'link_table.user_id = main_table.user_id',
            ['username']
        )->where('execution_time != ?', History::IMPORT_VALIDATION);

        return $this;
    }
}
