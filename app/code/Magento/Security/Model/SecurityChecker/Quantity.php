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
 * @since 2.1.0
 */
class Quantity implements SecurityCheckerInterface
{
    /**
     * @var \Magento\Security\Model\ResourceModel\PasswordResetRequestEvent\CollectionFactory
     * @since 2.1.0
     */
    protected $collectionFactory;

    /**
     * @var ConfigInterface
     * @since 2.1.0
     */
    protected $securityConfig;

    /**
     * @var RemoteAddress
     * @since 2.1.0
     */
    private $remoteAddress;

    /**
     * @param ConfigInterface $securityConfig
     * @param CollectionFactory $collectionFactory
     * @param RemoteAddress $remoteAddress
     * @since 2.1.0
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
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function check($securityEventType, $accountReference = null, $longIp = null)
    {
        $isEnabled = $this->securityConfig->getPasswordResetProtectionType() != ResetMethod::OPTION_NONE;
        $allowedAttemptsNumber = $this->securityConfig->getMaxNumberPasswordResetRequests();
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
     * @since 2.1.0
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
