<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Model\ResourceModel\Oauth;

/**
 * oAuth nonce resource model
 *
 * @author Magento Core Team <core@magentocommerce.com>
 */
class Nonce extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('oauth_nonce', null);
    }

    /**
     * Delete old entries
     *
     * @param int $minutes Delete entries older than
     * @return int
     */
    public function deleteOldEntries($minutes)
    {
        if ($minutes > 0) {
            $connection = $this->getConnection();

            return $connection->delete(
                $this->getMainTable(),
                $connection->quoteInto('timestamp <= ?', time() - $minutes * 60, \Zend_Db::INT_TYPE)
            );
        } else {
            return 0;
        }
    }

    /**
     * Select a unique nonce row using a composite primary key (i.e. $nonce and $consumerId)
     *
     * @param string $nonce - The nonce string
     * @param int $consumerId - The consumer id
     * @return array - Array of data
     */
    public function selectByCompositeKey($nonce, $consumerId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getMainTable()
        )->where(
            'nonce = ?',
            $nonce
        )->where(
            'consumer_id = ?',
            $consumerId
        );
        $row = $connection->fetchRow($select);
        return $row ? $row : [];
    }
}
