<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerWebapi\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Integration\Model\Oauth\TokenFactory;
use Magento\LoginAsCustomerApi\Api\ConfigInterface;
use Magento\LoginAsCustomerWebapi\Api\CreateCustomerAccessTokenInterface;

/**
 * @inheritdoc
 */
class CreateCustomerAccessToken implements CreateCustomerAccessTokenInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var TokenFactory
     */
    private $tokenFactory;

    /**
     * @param ConfigInterface $config
     * @param CustomerRepositoryInterface $customerRepository
     * @param ManagerInterface $eventManager
     * @param TokenFactory $tokenFactory
     */
    public function __construct(
        ConfigInterface $config,
        CustomerRepositoryInterface $customerRepository,
        ManagerInterface $eventManager,
        TokenFactory $tokenFactory
    ) {
        $this->config = $config;
        $this->customerRepository = $customerRepository;
        $this->eventManager = $eventManager;
        $this->tokenFactory = $tokenFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute(int $customerId): string
    {
        if ($this->config->isEnabled()) {
            $customer = $this->customerRepository->getById($customerId);
            $this->eventManager->dispatch('customer_login', ['customer' => $customer]);

            return $this->tokenFactory->create()->createCustomerToken($customerId)->getToken();
        } else {
            throw new LocalizedException(__('Service is disabled.'));
        }
    }
}
