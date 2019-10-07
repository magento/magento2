<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Model\ResourceModel\Oauth\Token;

use Magento\Customer\Model\Config\Share;
use Magento\Framework\App\ObjectManager;
use Magento\Integration\Model\Oauth\Token\RequestLog\ReaderInterface;
use Magento\Integration\Model\Oauth\Token\RequestLog\WriterInterface;
use Magento\Integration\Model\Oauth\Token\RequestLog\Config as RequestLogConfig;
use Magento\Integration\Model\Oauth\Token\RequestThrottler;
use Magento\Store\Model\StoreManagerInterface;

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
     * @var StoreManagerInterface|null
     */
    private $storeManager;

    /**
     * @var Share
     */
    private $share;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param RequestLogConfig $requestLogConfig
     * @param string|null $connectionName
     * @param StoreManagerInterface|null $storeManager
     * @param Share $share
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        RequestLogConfig $requestLogConfig,
        $connectionName = null,
        StoreManagerInterface $storeManager = null,
        Share $share = null
    ) {
        parent::__construct($context, $connectionName);

        $this->dateTime = $dateTime;
        $this->requestLogConfig = $requestLogConfig;
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()->get(StoreManagerInterface::class);
        $this->share = $share ?: ObjectManager::getInstance()->get(Share::class);
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
     */
    public function getFailuresCount($userName, $userType)
    {
        if ($userType === RequestThrottler::USER_TYPE_CUSTOMER && (bool)$this->share->isWebsiteScope() === true) {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
            $whereString = 'user_name = :user_name AND user_type = :user_type AND website_id = :website_id';
            $bind = ['user_name' => $userName, 'user_type' => $userType, 'website_id' => $websiteId];
        } else {
            $whereString = 'user_name = :user_name AND user_type = :user_type';
            $bind = ['user_name' => $userName, 'user_type' => $userType];
        }

        $select = $this->getConnection()->select();
        $select->from($this->getMainTable(), 'failures_count')
            ->where($whereString);

        return (int)$this->getConnection()->fetchOne($select, $bind);
    }

    /**
     * @inheritdoc
     */
    public function resetFailuresCount($userName, $userType)
    {
        if ($userType === RequestThrottler::USER_TYPE_CUSTOMER && (bool)$this->share->isWebsiteScope() === true) {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();

            $this->getConnection()->delete(
                $this->getMainTable(),
                ['user_name = ?' => $userName, 'user_type = ?' => $userType, 'website_id = ?' => $websiteId]
            );
        } else {
            $this->getConnection()->delete(
                $this->getMainTable(),
                ['user_name = ?' => $userName, 'user_type = ?' => $userType]
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function incrementFailuresCount($userName, $userType)
    {
        $date = (new \DateTime())->setTimestamp($this->dateTime->gmtTimestamp());
        $date->add(new \DateInterval('PT' . $this->requestLogConfig->getLockTimeout() . 'S'));
        $dateTime = $date->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);

        if ($userType === RequestThrottler::USER_TYPE_CUSTOMER && (bool)$this->share->isWebsiteScope() === true) {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();

            $data = [
                'user_name' => $userName,
                'user_type' => $userType,
                'failures_count' => 1,
                'lock_expires_at' => $dateTime,
                'website_id' => (int)$websiteId,
            ];
        } else {
            $data = [
                'user_name' => $userName,
                'user_type' => $userType,
                'failures_count' => 1,
                'lock_expires_at' => $dateTime,
            ];
        }

        $this->getConnection()->insertOnDuplicate(
            $this->getMainTable(),
            $data,
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
