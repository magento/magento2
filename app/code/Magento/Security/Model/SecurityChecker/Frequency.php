<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Model\SecurityChecker;

use Magento\Framework\Exception\SecurityViolationException;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Security\Model\Config\Source\ResetMethod;
use Magento\Security\Model\ConfigInterface;
use Magento\Security\Model\ResourceModel\PasswordResetRequestEvent\CollectionFactory;

/**
 * Checker by frequency requests
 * @since 2.1.0
 */
class Frequency implements SecurityCheckerInterface
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     * @since 2.1.0
     */
    private $dateTime;

    /**
     * @var \Magento\Security\Model\ResourceModel\PasswordResetRequestEvent\CollectionFactory
     * @since 2.1.0
     */
    private $collectionFactory;

    /**
     * @var ConfigInterface
     * @since 2.1.0
     */
    private $securityConfig;

    /**
     * @var RemoteAddress
     * @since 2.1.0
     */
    private $remoteAddress;

    /**
     * @param ConfigInterface $securityConfig
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param RemoteAddress $remoteAddress
     * @since 2.1.0
     */
    public function __construct(
        ConfigInterface $securityConfig,
        CollectionFactory $collectionFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        RemoteAddress $remoteAddress
    ) {
        $this->securityConfig = $securityConfig;
        $this->collectionFactory = $collectionFactory;
        $this->dateTime = $dateTime;
        $this->remoteAddress = $remoteAddress;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function check($securityEventType, $accountReference = null, $longIp = null)
    {
        $isEnabled = $this->securityConfig->getPasswordResetProtectionType() != ResetMethod::OPTION_NONE;
        $limitTimeBetweenRequests = $this->securityConfig->getMinTimeBetweenPasswordResetRequests();
        if ($isEnabled && $limitTimeBetweenRequests) {
            if (null === $longIp) {
                $longIp = $this->remoteAddress->getRemoteAddress();
            }
            $lastRecordCreationTimestamp = $this->loadLastRecordCreationTimestamp(
                $securityEventType,
                $accountReference,
                $longIp
            );
            if ($lastRecordCreationTimestamp && (
                    $limitTimeBetweenRequests >
                    ($this->dateTime->gmtTimestamp() - $lastRecordCreationTimestamp)
                )) {
                throw new SecurityViolationException(
                    __(
                        'Too many password reset requests. Please wait and try again or contact %1.',
                        $this->securityConfig->getCustomerServiceEmail()
                    )
                );
            }
        }
    }

    /**
     * Load last record creation timestamp
     *
     * @param int $securityEventType
     * @param string $accountReference
     * @param int $longIp
     * @return int
     * @since 2.1.0
     */
    private function loadLastRecordCreationTimestamp($securityEventType, $accountReference, $longIp)
    {
        $collection = $this->collectionFactory->create($securityEventType, $accountReference, $longIp);
        /** @var \Magento\Security\Model\PasswordResetRequestEvent $record */
        $record = $collection->filterLastItem()->getFirstItem();

        return (int) strtotime($record->getCreatedAt());
    }
}
