<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Model\SecurityChecker;

use Magento\Security\Model\Config\Source\ResetMethod;
use Magento\Security\Model\PasswordResetRequestEvent;
use Magento\Security\Model\ResourceModel\PasswordResetRequestEvent\CollectionFactory as RequestCollectionFactory;
use Magento\Security\Model\ResourceModel\PasswordResetRequestEvent\Collection as RequestCollection;

/**
 * Abstract security checker class
 */
abstract class AbstractSecurityChecker
{
    /**
     * @var \Magento\Security\Model\ResourceModel\PasswordResetRequestEvent\CollectionFactory
     */
    protected $passwordResetRequestEventCollectionFactory;

    /**
     * @var \Magento\Security\Helper\SecurityConfig
     */
    protected $securityConfig;

    /**
     * @param \Magento\Security\Helper\SecurityConfig $securityConfig
     * @param RequestCollectionFactory $passwordResetRequestEventCollectionFactory
     */
    public function __construct(
        \Magento\Security\Helper\SecurityConfig $securityConfig,
        RequestCollectionFactory $passwordResetRequestEventCollectionFactory
    ) {
        $this->securityConfig = $securityConfig;
        $this->passwordResetRequestEventCollectionFactory = $passwordResetRequestEventCollectionFactory;
    }

    /**
     * Create collection
     *
     * @param int $securityEventType
     * @return \Magento\Security\Model\ResourceModel\PasswordResetRequestEvent\Collection
     */
    protected function createCollection($securityEventType)
    {
        /** @var RequestCollection $passwordResetRequestEventCollection */
        $passwordResetRequestEventCollection = $this->passwordResetRequestEventCollectionFactory->create();
        $passwordResetRequestEventCollection->filterByRequestType($securityEventType);

        return $passwordResetRequestEventCollection;
    }

    /**
     * Apply config filters
     *
     * @param RequestCollection $passwordResetRequestEventCollection
     * @param int $securityEventType
     * @param string $accountReference
     * @param int $longIp
     * @return RequestCollection
     */
    protected function applyFiltersByConfig(
        RequestCollection $passwordResetRequestEventCollection,
        $securityEventType,
        $accountReference,
        $longIp
    ) {
        $limitMethod = $this->securityConfig->getLimitPasswordResetRequestsMethod(
            $this->getScopeByEventType($securityEventType)
        );
        switch ($limitMethod) {
            case ResetMethod::OPTION_BY_EMAIL:
                $passwordResetRequestEventCollection->filterByAccountReference($accountReference);
                break;
            case ResetMethod::OPTION_BY_IP:
                $passwordResetRequestEventCollection->filterByIp($longIp);
                break;
            case ResetMethod::OPTION_BY_IP_AND_EMAIL:
                $passwordResetRequestEventCollection->filterByIpOrAccountReference($longIp, $accountReference);
                break;
        }

        return $passwordResetRequestEventCollection;
    }

    /**
     * Get scope by event type
     *
     * @param int $eventType
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getScopeByEventType($eventType)
    {
        switch ($eventType) {
            case PasswordResetRequestEvent::CUSTOMER_PASSWORD_RESET_REQUEST:
                return \Magento\Security\Helper\SecurityConfig::FRONTED_AREA_SCOPE;
            case PasswordResetRequestEvent::ADMIN_PASSWORD_RESET_REQUEST:
                return \Magento\Security\Helper\SecurityConfig::ADMIN_AREA_SCOPE;
            default:
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Security module: Unknown security event type')
                );
        }
    }
}
