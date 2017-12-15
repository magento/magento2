<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Model\ResourceModel\Oauth;

use Magento\Authorization\Model\UserContextInterface;

/**
 * OAuth token resource model
 */
class Token extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $_dateTime;

    /**
     * Date
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        $connectionName = null
    ) {
        $this->_dateTime = $dateTime;
        $this->date = $date;
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('oauth_token', 'entity_id');
    }

    /**
     * Clean up old authorized tokens for specified consumer-user pairs
     *
     * @param \Magento\Integration\Model\Oauth\Token $exceptToken Token just created to exclude from delete
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return int The number of affected rows
     */
    public function cleanOldAuthorizedTokensExcept(\Magento\Integration\Model\Oauth\Token $exceptToken)
    {
        if (!$exceptToken->getId() || !$exceptToken->getAuthorized()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid token to except'));
        }
        $connection = $this->getConnection();
        $where = $connection->quoteInto(
            'authorized = 1 AND consumer_id = ?',
            $exceptToken->getConsumerId(),
            \Zend_Db::INT_TYPE
        );
        $where .= $connection->quoteInto(' AND entity_id <> ?', $exceptToken->getId(), \Zend_Db::INT_TYPE);

        if ($exceptToken->getCustomerId()) {
            $where .= $connection->quoteInto(' AND customer_id = ?', $exceptToken->getCustomerId(), \Zend_Db::INT_TYPE);
        } elseif ($exceptToken->getAdminId()) {
            $where .= $connection->quoteInto(' AND admin_id = ?', $exceptToken->getAdminId(), \Zend_Db::INT_TYPE);
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid token to except'));
        }
        return $connection->delete($this->getMainTable(), $where);
    }

    /**
     * Delete old entries
     *
     * @param int $minutes
     * @return int
     */
    public function deleteOldEntries($minutes)
    {
        if ($minutes > 0) {
            $connection = $this->getConnection();

            return $connection->delete(
                $this->getMainTable(),
                $connection->quoteInto(
                    'type = "' . \Magento\Integration\Model\Oauth\Token::TYPE_REQUEST . '" AND created_at <= ?',
                    $this->_dateTime->formatDate($this->date->gmtTimestamp() - $minutes * 60)
                )
            );
        } else {
            return 0;
        }
    }

    /**
     * Delete expired tokens for the specified user types
     *
     * @param int $hours token lifetime
     * @param int[] $userTypes @see \Magento\Authorization\Model\UserContextInterface
     * @return int number of deleted tokens
     */
    public function deleteExpiredTokens($hours, $userTypes)
    {
        if ($hours > 0) {
            $connection = $this->getConnection();

            $userTypeCondition = $connection->quoteInto('user_type IN (?)', $userTypes);
            $createdAtCondition = $connection->quoteInto(
                'created_at <= ?',
                $this->_dateTime->formatDate($this->date->gmtTimestamp() - $hours * 60 * 60)
            );
            return $connection->delete(
                $this->getMainTable(),
                $userTypeCondition . ' AND ' . $createdAtCondition
            );
        } else {
            return 0;
        }
    }

    /**
     * Select a single token of the specified type for the specified consumer.
     *
     * @param int $consumerId - The consumer id
     * @param string $type - The token type (e.g. 'verifier')
     * @return array|boolean - Row data (array) or false if there is no corresponding row
     */
    public function selectTokenByType($consumerId, $type)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable())
            ->where('consumer_id = ?', $consumerId)
            ->where('type = ?', $type);
        return $connection->fetchRow($select);
    }

    /**
     * Select token for a given consumer and user type.
     *
     * @param int $consumerId
     * @param int $userType
     * @return array|boolean - Row data (array) or false if there is no corresponding row
     */
    public function selectTokenByConsumerIdAndUserType($consumerId, $userType)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable())
            ->where('consumer_id = ?', (int)$consumerId)
            ->where('user_type = ?', (int)$userType);
        return $connection->fetchRow($select);
    }

    /**
     * Select token for a given admin id.
     *
     * @param int $adminId
     * @return array|boolean - Row data (array) or false if there is no corresponding row
     */
    public function selectTokenByAdminId($adminId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable())
            ->where('admin_id = ?', $adminId)
            ->where('user_type = ?', UserContextInterface::USER_TYPE_ADMIN);
        return $connection->fetchRow($select);
    }

    /**
     * Select token for a given customer.
     *
     * @param int $customerId
     * @return array|boolean - Row data (array) or false if there is no corresponding row
     */
    public function selectTokenByCustomerId($customerId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable())
            ->where('customer_id = ?', $customerId)
            ->where('user_type = ?', UserContextInterface::USER_TYPE_CUSTOMER);
        return $connection->fetchRow($select);
    }
}
