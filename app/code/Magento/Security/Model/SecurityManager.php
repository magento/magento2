<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Model;

use Magento\Framework\Exception\SecurityViolationException;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Security\Model\SecurityChecker\SecurityCheckerInterface;

/**
 * Manager for password reset actions
 *
 * @api
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.1.0
 */
class SecurityManager
{
    /**
     * Security control records time life
     */
    const SECURITY_CONTROL_RECORDS_LIFE_TIME =  86400;

    /**
     * @var ConfigInterface
     * @since 100.1.0
     */
    protected $securityConfig;

    /**
     * @var \Magento\Security\Model\PasswordResetRequestEventFactory
     * @since 100.1.0
     */
    protected $passwordResetRequestEventFactory;

    /**
     * @var ResourceModel\PasswordResetRequestEvent\CollectionFactory
     * @since 100.1.0
     */
    protected $passwordResetRequestEventCollectionFactory;

    /**
     * @var SecurityCheckerInterface[]
     * @since 100.1.0
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
            if (!($checker instanceof SecurityCheckerInterface)) {
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
     * @since 100.1.0
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
     * @since 100.1.0
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
     * @since 100.1.0
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
