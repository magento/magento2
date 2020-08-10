<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerApi\Model;

use Magento\LoginAsCustomerApi\Api\ConfigInterface;
use Magento\LoginAsCustomerApi\Api\Data\IsLoginAsCustomerEnabledForCustomerResultInterface;
use Magento\LoginAsCustomerApi\Api\Data\IsLoginAsCustomerEnabledForCustomerResultInterfaceFactory;
use Magento\LoginAsCustomerApi\Api\IsLoginAsCustomerEnabledForCustomerInterface;

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
     * @var IsLoginAsCustomerEnabledForCustomerResultInterfaceFactory
     */
    private $resultFactory;

    /**
     * @var IsLoginAsCustomerEnabledForCustomerResultInterface[]
     */
    private $resolvers;

    /**
     * @param ConfigInterface $config
     * @param IsLoginAsCustomerEnabledForCustomerResultInterfaceFactory $resultFactory
     * @param array $resolvers
     */
    public function __construct(
        ConfigInterface $config,
        IsLoginAsCustomerEnabledForCustomerResultInterfaceFactory $resultFactory,
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
        /** @var IsLoginAsCustomerEnabledForCustomerInterface $resolver */
        foreach ($this->resolvers as $resolver) {
            $resolverResult = $resolver->execute($customerId);
            if (!$resolverResult->isEnabled()) {
                $messages[] = $resolverResult->getMessages();
            }
        }

        return $this->resultFactory->create(['messages' => array_merge(...$messages)]);
    }
}
