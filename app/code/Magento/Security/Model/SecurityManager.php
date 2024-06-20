<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Model;

use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\SecurityViolationException;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Stdlib\DateTime\DateTime;
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
     * SecurityManager constructor.
     *
     * @param ConfigInterface $securityConfig
     * @param PasswordResetRequestEventFactory $passwordResetRequestEventFactory
     * @param ResourceModel\PasswordResetRequestEvent\CollectionFactory $passwordResetRequestEventCollectionFactory
     * @param EventManagerInterface $eventManager
     * @param DateTime $dateTime
     * @param RemoteAddress $remoteAddress
     * @param SecurityCheckerInterface[] $securityCheckers
     * @throws LocalizedException
     */
    public function __construct(
        protected readonly ConfigInterface $securityConfig,
        protected readonly PasswordResetRequestEventFactory $passwordResetRequestEventFactory,
        protected readonly ResourceModel\PasswordResetRequestEvent\CollectionFactory $passwordResetRequestEventCollectionFactory,
        private readonly EventManagerInterface $eventManager,
        private readonly DateTime $dateTime,
        private readonly RemoteAddress $remoteAddress,
        protected $securityCheckers = []
    ) {
        foreach ($this->securityCheckers as $checker) {
            if (!($checker instanceof SecurityCheckerInterface)) {
                throw new LocalizedException(
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
        /** @var PasswordResetRequestEventFactory $passwordResetRequestEvent */
        $passwordResetRequestEvent = $this->passwordResetRequestEventFactory->create();
        $passwordResetRequestEvent->setRequestType($requestType)
            ->setAccountReference($accountReference)
            ->setIp($longIp)
            ->save();

        return $passwordResetRequestEvent;
    }
}
