<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Model;

use Magento\Framework\Exception\SecurityViolationException;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;

/**
 * Security Control Manager Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SecurityManager
{
    /**
     * Security control records time life
     */
    const SECURITY_CONTROL_RECORDS_LIFE_TIME =  86400;

    /**
     * @var ConfigInterface
     */
    protected $securityConfig;

    /**
     * @var \Magento\Security\Model\PasswordResetRequestEventFactory
     */
    protected $passwordResetRequestEventFactory;

    /**
     * @var ResourceModel\PasswordResetRequestEvent\CollectionFactory
     */
    protected $passwordResetRequestEventCollectionFactory;

    /**
     * @var array
     */
    protected $securityCheckers;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    /**
     * @var RemoteAddress
     */
    private $remoteAddress;

    /**
     * SecurityManager constructor.
     *
     * @param ConfigInterface $securityConfig
     * @param \Magento\Security\Model\PasswordResetRequestEventFactory $passwordResetRequestEventFactory
     * @param ResourceModel\PasswordResetRequestEvent\CollectionFactory $passwordResetRequestEventCollectionFactory
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param RemoteAddress $remoteAddress
     * @param array $securityCheckers
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        ConfigInterface $securityConfig,
        \Magento\Security\Model\PasswordResetRequestEventFactory $passwordResetRequestEventFactory,
        ResourceModel\PasswordResetRequestEvent\CollectionFactory $passwordResetRequestEventCollectionFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        RemoteAddress $remoteAddress,
        $securityCheckers = []
    ) {
        $this->securityConfig = $securityConfig;
        $this->passwordResetRequestEventFactory = $passwordResetRequestEventFactory;
        $this->passwordResetRequestEventCollectionFactory = $passwordResetRequestEventCollectionFactory;
        $this->securityCheckers = $securityCheckers;
        $this->eventManager = $eventManager;
        $this->dateTime = $dateTime;
        $this->remoteAddress = $remoteAddress;

        foreach ($this->securityCheckers as $checker) {
            if (!($checker instanceof \Magento\Security\Model\SecurityChecker\SecurityCheckerInterface)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Incorrect Security Checker class. It has to implement SecurityCheckerInterface')
                );
            }
        }
    }

    /**
     * Perform security check
     *
     * @param int $requestType
     * @param string|null $accountReference
     * @param int|null $longIp
     * @return $this
     * @throws SecurityViolationException
     */
    public function performSecurityCheck($requestType, $accountReference = null, $longIp = null)
    {
        if (null === $longIp) {
            $longIp = $this->remoteAddress->getRemoteAddress();
        }
        foreach ($this->securityCheckers as $checker) {
            $checker->check($requestType, $accountReference, $longIp);
        }

        $this->createNewPasswordResetRequestEventRecord($requestType, $accountReference, $longIp);

        return $this;
    }

    /**
     * Clean expired Admin Sessions
     *
     * @return $this
     */
    public function cleanExpiredRecords()
    {
        $this->passwordResetRequestEventCollectionFactory->create()->deleteRecordsOlderThen(
            $this->dateTime->gmtTimestamp() - self::SECURITY_CONTROL_RECORDS_LIFE_TIME
        );

        return $this;
    }

    /**
     * Create new password reset request record
     *
     * @param int $requestType
     * @param string|null $accountReference
     * @param int $longIp
     * @return PasswordResetRequestEvent
     */
    protected function createNewPasswordResetRequestEventRecord($requestType, $accountReference, $longIp)
    {
        /** @var \Magento\Security\Model\PasswordResetRequestEventFactory $passwordResetRequestEvent */
        $passwordResetRequestEvent = $this->passwordResetRequestEventFactory->create();
        $passwordResetRequestEvent->setRequestType($requestType)
            ->setAccountReference($accountReference)
            ->setIp($longIp)
            ->save();

        return $passwordResetRequestEvent;
    }
}
