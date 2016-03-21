<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Model\ResourceModel\Oauth\Token;

use Magento\Integration\Model\Oauth\Token\RequestLog\ReaderInterface;
use Magento\Integration\Model\Oauth\Token\RequestLog\WriterInterface;
use Magento\Integration\Model\Oauth\Token\RequestLog\Config as RequestLogConfig;

/**
 * Resource model for failed authentication attempts to retrieve admin/customer token.
 */
class RequestLog extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
    implements ReaderInterface, WriterInterface
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    /**
     * @var RequestLogConfig
     */
    private $requestLogConfig;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param RequestLogConfig $requestLogConfig
     * @param string|null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        RequestLogConfig $requestLogConfig,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->dateTime = $dateTime;
        $this->requestLogConfig = $requestLogConfig;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('oauth_token_request_log', 'entity_id');
    }

    /**
     * {@inheritdoc}
     */
    public function getFailuresCount($userName, $userType)
    {
        $select = $this->getConnection()->select();
        $select->columns('failures_count')
            ->from($this->getMainTable())
            ->where('user_login = ? AND user_type = ?', [$userName, $userType]);

        return (int)$this->getConnection()->fetchOne($select);
    }

    /**
     * {@inheritdoc}
     */
    public function resetFailuresCount($userName, $userType)
    {
        $this->getConnection()->delete(
            $this->getMainTable(),
            ['user_login = ?' => $userName, 'user_type = ?' => $userType]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function incrementFailuresCount($userName, $userType)
    {
        $date = (new \DateTime())->setTimestamp($this->dateTime->gmtTimestamp());
        $date->add(new \DateInterval('PT' . $this->requestLogConfig->getLockTimeout() . 'S'));
        $this->getConnection()->insertOnDuplicate(
            $this->getMainTable(),
            [
                'user_login' => $userName,
                'user_type' => $userType,
                'failures_count' => 1,
                'lock_expires_at' => $date
            ],
            ['failures_count' => new \Zend_Db_Expr('failures_count+1'), 'lock_expires_at' => $date]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function clearExpiredFailures()
    {
        $select = $this->getConnection()->select();
        $select->from($this->getMainTable())->where('lock_expires_at <= ?', $this->dateTime->gmtTimestamp());
        $this->getConnection()->delete($select);
    }
}
