<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Model\ResourceModel\Oauth\Token;

use Magento\Integration\Model\Oauth\Token\RequestLog\ReaderInterface;
use Magento\Integration\Model\Oauth\Token\RequestLog\WriterInterface;
use Magento\Integration\Model\Oauth\Token\RequestLog\Config as RequestLogConfig;

/**
 * Resource model for failed authentication attempts to retrieve admin/customer token.
 */
class RequestLog extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb implements
    ReaderInterface,
    WriterInterface
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
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('oauth_token_request_log', 'entity_id');
    }

    /**
     * @inheritdoc
     * @param string $userName
     * @param int $userType
     */
    public function getFailuresCount($userName, $userType)
    {
        $date = (new \DateTime())->setTimestamp($this->dateTime->gmtTimestamp());
        $dateTime = $date->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
        
        $select = $this->getConnection()->select();
        $select->from($this->getMainTable(), 'failures_count')
            ->where('user_name = :user_name AND user_type = :user_type AND lock_expires_at > :expiration_time');

        return (int)$this->getConnection()->fetchOne(
            $select,
            [
                'user_name' => $userName,
                'user_type' => $userType,
                'expiration_time' => $dateTime,
            ]
        );
    }

    /**
     * @inheritdoc
     * @param string $userName
     * @param int $userType
     */
    public function resetFailuresCount($userName, $userType)
    {
        $this->getConnection()->delete(
            $this->getMainTable(),
            ['user_name = ?' => $userName, 'user_type = ?' => $userType]
        );
    }

    /**
     * @inheritdoc
     * @param string $userName
     * @param int $userType
     */
    public function incrementFailuresCount($userName, $userType)
    {
        $date = (new \DateTime())->setTimestamp($this->dateTime->gmtTimestamp());
        $date->add(new \DateInterval('PT' . $this->requestLogConfig->getLockTimeout() . 'S'));
        $dateTime = $date->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);

        $this->getConnection()->insertOnDuplicate(
            $this->getMainTable(),
            [
                'user_name' => $userName,
                'user_type' => $userType,
                'failures_count' => 1,
                'lock_expires_at' => $dateTime
            ],
            [
                'failures_count' => new \Zend_Db_Expr('failures_count+1'),
                'lock_expires_at' => new \Zend_Db_Expr("'" . $dateTime . "'")
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function clearExpiredFailures()
    {
        $date = (new \DateTime())->setTimestamp($this->dateTime->gmtTimestamp());
        $dateTime = $date->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
        $this->getConnection()->delete($this->getMainTable(), ['lock_expires_at <= ?' => $dateTime]);
    }
}
