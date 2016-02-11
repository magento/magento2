<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Model\SecurityChecker;

use Magento\Framework\Exception\SecurityViolationException;
use Magento\Security\Model\Config\Source\ResetMethod;

/**
 * Checker by frequency requests
 */
class Frequency extends AbstractSecurityChecker implements SecurityCheckerInterface
{
    /**
     * {@inheritdoc}
     */
    public function check($securityEventType, $accountReference = null, $longIp = null)
    {
        if (null === $longIp) {
            $longIp = $this->securityConfig->getRemoteIp();
        }
        $scope = $this->getScopeByEventType($securityEventType);

        $isEnabled = $this->securityConfig->getLimitPasswordResetRequestsMethod($scope) != ResetMethod::OPTION_NONE;
        $limitTimeBetweenRequests = $this->securityConfig->getLimitTimeBetweenPasswordResetRequests($scope);
        if ($isEnabled && $limitTimeBetweenRequests) {
            $lastRecordCreationTimestamp = $this->loadLastRecordCreationTimestamp(
                $securityEventType,
                $accountReference,
                $longIp
            );
            if ($lastRecordCreationTimestamp && (
                    $limitTimeBetweenRequests >
                    ($this->securityConfig->getCurrentTimestamp() - $lastRecordCreationTimestamp)
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
     */
    protected function loadLastRecordCreationTimestamp($securityEventType, $accountReference, $longIp)
    {
        $collection = $this->createCollection($securityEventType);
        $this->applyFiltersByConfig($collection, $securityEventType, $accountReference, $longIp);
        /** @var \Magento\Security\Model\PasswordResetRequestEvent $record */
        $record = $collection->filterLastItem()->getFirstItem();

        return (int) strtotime($record->getCreatedAt());
    }
}
