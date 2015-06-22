<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Model\Resource;

/**
 * Class History
 */
class History extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('import_history', 'history_id');
    }

    /**
     * Retrieve last inserted report id by user id
     *
     * @param string $userId
     * @return int $lastId
     */
    public function getLastInsertedId($userId)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter
            ->select()
            ->from($this->getMainTable())
            ->order($this->getIdFieldName() . ' DESC')
            ->where('user_id = ?', $userId)
            ->limit(1);

        return $adapter->fetchOne($select);
    }
}
