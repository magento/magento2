<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Model\SecurityChecker;

use Magento\Framework\Exception\SecurityViolationException;
use Magento\Security\Model\Config\Source\ResetMethod;

/**
 * Check by requests number per fixed period of time
 */
class Quantity extends AbstractSecurityChecker implements SecurityCheckerInterface
{
    /**
     * {@inheritdoc}
     */
    public function check($securityEventType, $accountReference = null, $longIp = null)
    {
        $scope = $this->getScopeByEventType($securityEventType);
        $isEnabled = $this->securityConfig->getLimitPasswordResetRequestsMethod($scope) != ResetMethod::OPTION_NONE;
        $allowedAttemptsNumber = $this->securityConfig->getLimitNumberPasswordResetRequests($scope);
        if ($isEnabled and $allowedAttemptsNumber) {
            $collection = $this->prepareCollection($securityEventType, $accountReference, $longIp);
            if ($collection->count() >= $allowedAttemptsNumber) {
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
     * Prepare collection
     *
     * @param int $securityEventType
     * @param string $accountReference
     * @param int $longIp
     * @return \Magento\Security\Model\ResourceModel\PasswordResetRequestEvent\Collection
     */
    protected function prepareCollection($securityEventType, $accountReference, $longIp)
    {
        $collection = $this->createCollection($securityEventType);
        if (null === $longIp) {
            $longIp = $this->securityConfig->getRemoteIp();
        }
        $this->applyFiltersByConfig($collection, $securityEventType, $accountReference, $longIp);
        $periodToCheck = $this->securityConfig->getTimePeriodToCalculateLimitations();
        $collection->filterByLifetime($periodToCheck);

        return $collection;
    }
}
