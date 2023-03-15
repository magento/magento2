<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Model\ResourceModel\PasswordResetRequestEvent;

use Magento\Framework\ObjectManagerInterface;
use Magento\Security\Model\Config\Source\ResetMethod;
use Magento\Security\Model\ConfigInterface;

/**
 * Factory class for @see Collection
 *
 * @api
 * @since 100.1.0
 */
class CollectionFactory
{
    /**
     * CollectionFactory constructor.
     *
     * @param ObjectManagerInterface $objectManager
     * @param ConfigInterface $securityConfig
     * @param string $instanceName Instance name to create
     */
    public function __construct(
        protected readonly ObjectManagerInterface $objectManager,
        protected readonly ConfigInterface $securityConfig,
        protected $instanceName = Collection::class
    ) {
    }

    /**
     * Create class instance with specified parameters
     *
     * @param int $securityEventType
     * @param string $accountReference
     * @param string $longIp
     * @return Collection
     * @since 100.1.0
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
