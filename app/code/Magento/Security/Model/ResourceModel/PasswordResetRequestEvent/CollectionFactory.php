<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Model\ResourceModel\PasswordResetRequestEvent;

use Magento\Security\Model\Config\Source\ResetMethod;
use Magento\Security\Model\ConfigInterface;

/**
 * Factory class for @see
 * \Magento\Security\Model\ResourceModel\PasswordResetRequestEvent\Collection
 */
class CollectionFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager = null;

    /**
     * Instance name to create
     *
     * @var string
     */
    protected $instanceName = null;

    /**
     * @var ConfigInterface
     */
    protected $securityConfig;

    /**
     * CollectionFactory constructor.
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param ConfigInterface $securityConfig
     * @param string $instanceName
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        ConfigInterface $securityConfig,
        $instanceName = Collection::class
    ) {
        $this->objectManager = $objectManager;
        $this->securityConfig = $securityConfig;
        $this->instanceName = $instanceName;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param int $securityEventType
     * @param string $accountReference
     * @param string $longIp
     * @return Collection
     */
    public function create(
        $securityEventType = null,
        $accountReference = null,
        $longIp = null
    ) {
        /** @var Collection $collection */
        $collection = $this->objectManager->create($this->instanceName);
        if (null !== $securityEventType) {
            $collection->filterByRequestType($securityEventType);

            switch ($this->securityConfig->getPasswordResetProtectionType()) {
                case ResetMethod::OPTION_BY_EMAIL:
                    $collection->filterByAccountReference($accountReference);
                    break;
                case ResetMethod::OPTION_BY_IP:
                    $collection->filterByIp($longIp);
                    break;
                case ResetMethod::OPTION_BY_IP_AND_EMAIL:
                    $collection->filterByIpOrAccountReference($longIp, $accountReference);
                    break;
                default:
            }
        }

        return $collection;
    }
}
