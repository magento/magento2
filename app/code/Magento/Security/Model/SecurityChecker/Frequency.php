<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Model\SecurityChecker;

use Magento\Framework\Exception\SecurityViolationException;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Stdlib\DateTime\DateTime as DateTimeModel;
use Magento\Security\Model\Config\Source\ResetMethod;
use Magento\Security\Model\ConfigInterface;
use Magento\Security\Model\PasswordResetRequestEvent;
use Magento\Security\Model\ResourceModel\PasswordResetRequestEvent\CollectionFactory;

/**
 * Checker by frequency requests
 */
class Frequency implements SecurityCheckerInterface
{
    /**
     * @param ConfigInterface $securityConfig
     * @param CollectionFactory $collectionFactory
     * @param DateTimeModel $dateTime
     * @param RemoteAddress $remoteAddress
     */
    public function __construct(
        private readonly ConfigInterface $securityConfig,
        private readonly CollectionFactory $collectionFactory,
        private readonly DateTimeModel $dateTime,
        private readonly RemoteAddress $remoteAddress
    ) {
    }

    /**
     * @inheritdoc
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
                        'We received too many requests for password resets. '
                        . 'Please wait and try again later or contact %1.',
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
     */
    private function loadLastRecordCreationTimestamp($securityEventType, $accountReference, $longIp)
    {
        $collection = $this->collectionFactory->create($securityEventType, $accountReference, $longIp);
        /** @var PasswordResetRequestEvent $record */
        $record = $collection->filterLastItem()->getFirstItem();

        return (int) strtotime($record->getCreatedAt() ?? '');
    }
}
