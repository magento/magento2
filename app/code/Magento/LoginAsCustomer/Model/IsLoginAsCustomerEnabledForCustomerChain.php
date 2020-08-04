<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomer\Model;

use Magento\LoginAsCustomerApi\Api\ConfigInterface;
use Magento\LoginAsCustomerApi\Api\Data\IsLoginAsCustomerEnabledForCustomerResultInterface;
use Magento\LoginAsCustomerApi\Api\IsLoginAsCustomerEnabledForCustomerInterface;
use Magento\LoginAsCustomerApi\Model\IsLoginAsCustomerEnabledForCustomerResolverInterface;

/**
 * @inheritdoc
 */
class IsLoginAsCustomerEnabledForCustomerChain implements IsLoginAsCustomerEnabledForCustomerInterface
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
     * @var IsLoginAsCustomerEnabledForCustomerResultInterface[]
     */
    private $resolvers;

    /**
     * @param ConfigInterface $config
     * @param IsLoginAsCustomerEnabledForCustomerResultFactory $resultFactory
     * @param array $resolvers
     */
    public function __construct(
        ConfigInterface $config,
        IsLoginAsCustomerEnabledForCustomerResultFactory $resultFactory,
        array $resolvers = []
    ) {
        $this->config = $config;
        $this->resultFactory = $resultFactory;
        $this->resolvers = $resolvers;
    }

    /**
     * @inheritdoc
     */
    public function execute(int $customerId): IsLoginAsCustomerEnabledForCustomerResultInterface
    {
        $messages = [[]];
        /** @var IsLoginAsCustomerEnabledForCustomerResultInterface $resolver */
        foreach ($this->resolvers as $resolver) {
            $resolverResult = $resolver->execute($customerId);
            if (!$resolverResult->isEnabled()) {
                $messages[] = $resolverResult->getMessages();
            }
        }

        return $this->resultFactory->create(['messages' => array_merge(...$messages)]);
    }
}
