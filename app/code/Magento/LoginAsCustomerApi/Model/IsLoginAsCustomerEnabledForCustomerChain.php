<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerApi\Model;

use Magento\LoginAsCustomerApi\Api\Data\IsLoginAsCustomerEnabledForCustomerResultInterface;
use Magento\LoginAsCustomerApi\Api\Data\IsLoginAsCustomerEnabledForCustomerResultInterfaceFactory;
use Magento\LoginAsCustomerApi\Api\IsLoginAsCustomerEnabledForCustomerInterface;

/**
 * @inheritdoc
 */
class IsLoginAsCustomerEnabledForCustomerChain implements IsLoginAsCustomerEnabledForCustomerInterface
{

    /**
     * @var IsLoginAsCustomerEnabledForCustomerResultInterfaceFactory
     */
    private $resultFactory;

    /**
     * @var IsLoginAsCustomerEnabledForCustomerResultInterface[]
     */
    private $resolvers;

    /**
     * @param IsLoginAsCustomerEnabledForCustomerResultInterfaceFactory $resultFactory
     * @param array $resolvers
     */
    public function __construct(
        IsLoginAsCustomerEnabledForCustomerResultInterfaceFactory $resultFactory,
        array $resolvers = []
    ) {
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
