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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Integration\Model\Resource\Oauth;

/**
 * oAuth nonce resource model
 *
 * @author Magento Core Team <core@magentocommerce.com>
 */
class Nonce extends \Magento\Framework\Model\Resource\Db\AbstractDb
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
            $adapter = $this->_getWriteAdapter();

            return $adapter->delete(
                $this->getMainTable(),
                $adapter->quoteInto('timestamp <= ?', time() - $minutes * 60, \Zend_Db::INT_TYPE)
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
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()->from(
            $this->getMainTable()
        )->where(
            'nonce = ?',
            $nonce
        )->where(
            'consumer_id = ?',
            $consumerId
        );
        $row = $adapter->fetchRow($select);
        return $row ? $row : array();
    }
}
