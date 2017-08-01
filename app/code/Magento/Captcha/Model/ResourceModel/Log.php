<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Model\ResourceModel;

/**
 * Log Attempts resource
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Log extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Type Remote Address
     */
    const TYPE_REMOTE_ADDRESS = 1;

    /**
     * Type User Login Name
     */
    const TYPE_LOGIN = 2;

    /**
     * Core Date
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     * @since 2.0.0
     */
    protected $_coreDate;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     * @since 2.0.0
     */
    protected $_remoteAddress;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $coreDate
     * @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
     * @param string $connectionName
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $coreDate,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        $connectionName = null
    ) {
        $this->_coreDate = $coreDate;
        $this->_remoteAddress = $remoteAddress;
        parent::__construct($context, $connectionName);
    }

    /**
     * Define main table
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_setMainTable('captcha_log');
    }

    /**
     * Save or Update count Attempts
     *
     * @param string|null $login
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function logAttempt($login)
    {
        if ($login != null) {
            $this->getConnection()->insertOnDuplicate(
                $this->getMainTable(),
                [
                    'type' => self::TYPE_LOGIN,
                    'value' => $login,
                    'count' => 1,
                    'updated_at' => $this->_coreDate->gmtDate()
                ],
                ['count' => new \Zend\Db\Sql\Expression('count+1'), 'updated_at']
            );
        }
        $ip = $this->_remoteAddress->getRemoteAddress();
        if ($ip != null) {
            $this->getConnection()->insertOnDuplicate(
                $this->getMainTable(),
                [
                    'type' => self::TYPE_REMOTE_ADDRESS,
                    'value' => $ip,
                    'count' => 1,
                    'updated_at' => $this->_coreDate->gmtDate()
                ],
                ['count' => new \Zend\Db\Sql\Expression('count+1'), 'updated_at']
            );
        }
        return $this;
    }

    /**
     * Delete User attempts by login
     *
     * @param string $login
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function deleteUserAttempts($login)
    {
        if ($login != null) {
            $this->getConnection()->delete(
                $this->getMainTable(),
                ['type = ?' => self::TYPE_LOGIN, 'value = ?' => $login]
            );
        }
        $ip = $this->_remoteAddress->getRemoteAddress();
        if ($ip != null) {
            $this->getConnection()->delete(
                $this->getMainTable(),
                ['type = ?' => self::TYPE_REMOTE_ADDRESS, 'value = ?' => $ip]
            );
        }

        return $this;
    }

    /**
     * Get count attempts by ip
     *
     * @return null|int
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function countAttemptsByRemoteAddress()
    {
        $ip = $this->_remoteAddress->getRemoteAddress();
        if (!$ip) {
            return 0;
        }
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getMainTable(),
            'count'
        )->where(
            'type = ?',
            self::TYPE_REMOTE_ADDRESS
        )->where(
            'value = ?',
            $ip
        );
        return $connection->fetchOne($select);
    }

    /**
     * Get count attempts by user login
     *
     * @param string $login
     * @return null|int
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function countAttemptsByUserLogin($login)
    {
        if (!$login) {
            return 0;
        }
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getMainTable(),
            'count'
        )->where(
            'type = ?',
            self::TYPE_LOGIN
        )->where(
            'value = ?',
            $login
        );
        return $connection->fetchOne($select);
    }

    /**
     * Delete attempts with expired in update_at time
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function deleteOldAttempts()
    {
        $this->getConnection()->delete(
            $this->getMainTable(),
            ['updated_at < ?' => $this->_coreDate->gmtDate(null, time() - 60 * 30)]
        );
    }
}
