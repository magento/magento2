<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Model\ResourceModel;

/**
 * Class History
 *
 * @api
 * @since 2.0.0
 */
class History extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     *
     * @return void
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getLastInsertedId($userId)
    {
        $connection = $this->getConnection();
        $select = $connection
            ->select()
            ->from($this->getMainTable())
            ->order($this->getIdFieldName() . ' DESC')
            ->where('user_id = ?', $userId)
            ->limit(1);

        return $connection->fetchOne($select);
    }
}
