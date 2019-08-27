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
 * Check by requests number per fixed period of time
 */
class Quantity implements SecurityCheckerInterface
{
    /**
     * @var \Magento\Security\Model\ResourceModel\PasswordResetRequestEvent\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var ConfigInterface
     */
    protected $securityConfig;

    /**
     * @var RemoteAddress
     */
    private $remoteAddress;

    /**
     * @param ConfigInterface $securityConfig
     * @param CollectionFactory $collectionFactory
     * @param RemoteAddress $remoteAddress
     */
    public function __construct(
        ConfigInterface $securityConfig,
        CollectionFactory $collectionFactory,
        RemoteAddress $remoteAddress
    ) {
        $this->securityConfig = $securityConfig;
        $this->collectionFactory = $collectionFactory;
        $this->remoteAddress = $remoteAddress;
    }

    /**
     * @inheritdoc
     */
    public function check($securityEventType, $accountReference = null, $longIp = null)
    {
        $isEnabled = $this->securityConfig->getPasswordResetProtectionType() != ResetMethod::OPTION_NONE;
        $allowedAttemptsNumber = $this->securityConfig->getMaxNumberPasswordResetRequests();
        if ($isEnabled && $allowedAttemptsNumber) {
            $collection = $this->prepareCollection($securityEventType, $accountReference, $longIp);
            if ($collection->count() >= $allowedAttemptsNumber) {
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
     * Prepare collection
     *
     * @param int $securityEventType
     * @param string $accountReference
     * @param int $longIp
     * @return \Magento\Security\Model\ResourceModel\PasswordResetRequestEvent\Collection
     */
    protected function prepareCollection($securityEventType, $accountReference, $longIp)
    {
        if (null === $longIp) {
            $longIp = $this->remoteAddress->getRemoteAddress();
        }
        $collection = $this->collectionFactory->create($securityEventType, $accountReference, $longIp);
        $periodToCheck = $this->securityConfig->getLimitationTimePeriod();
        $collection->filterByLifetime($periodToCheck);

        return $collection;
    }
}
