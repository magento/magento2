<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomer\Model\Resolver;

use Magento\LoginAsCustomer\Model\IsLoginAsCustomerEnabledForCustomerResultFactory;
use Magento\LoginAsCustomerApi\Api\ConfigInterface;
use Magento\LoginAsCustomerApi\Api\Data\IsLoginAsCustomerEnabledForCustomerResultInterface;
use Magento\LoginAsCustomerApi\Api\IsLoginAsCustomerEnabledForCustomerInterface;

/**
 * @inheritdoc
 */
class IsLoginAsCustomerEnabledResolver implements IsLoginAsCustomerEnabledForCustomerInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var IsLoginAsCustomerEnabledForCustomerResultFactory
     */
    private $resultFactory;

    /**
     * @param ConfigInterface $config
     * @param IsLoginAsCustomerEnabledForCustomerResultFactory $resultFactory
     */
    public function __construct(
        ConfigInterface $config,
        IsLoginAsCustomerEnabledForCustomerResultFactory $resultFactory
    ) {
        $this->config = $config;
        $this->resultFactory = $resultFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute(int $customerId): IsLoginAsCustomerEnabledForCustomerResultInterface
    {
        $messages = [];
        if (!$this->config->isEnabled()) {
            $messages[] = __('Login as Customer is disabled.');
        }

        return $this->resultFactory->create(['messages' => $messages]);
    }
}
