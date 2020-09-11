<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\StoreSwitcher;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Store\Model\StoreSwitcher\ContextInterface;
use Magento\Store\Model\StoreSwitcher\RedirectDataPreprocessorInterface;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Collect customer data to be redirected to target store
 */
class RedirectDataPreprocessor implements RedirectDataPreprocessorInterface
{
    /**
     * @var UserContextInterface
     */
    private $userContext;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @param CustomerRegistry $customerRegistry
     * @param UserContextInterface $userContext
     * @param LoggerInterface $logger
     */
    public function __construct(
        CustomerRegistry $customerRegistry,
        UserContextInterface $userContext,
        LoggerInterface $logger
    ) {
        $this->customerRegistry = $customerRegistry;
        $this->userContext = $userContext;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function process(ContextInterface $context, array $data): array
    {
        if ($this->userContext->getUserType() === UserContextInterface::USER_TYPE_CUSTOMER
            && $this->userContext->getUserId()
        ) {
            try {
                $customer = $this->customerRegistry->retrieve($this->userContext->getUserId());
                if (in_array($context->getTargetStore()->getId(), $customer->getSharedStoreIds())) {
                    $data['customer_id'] = (int) $customer->getId();
                }
            } catch (Throwable $e) {
                $this->logger->error($e);
            }
        }

        return $data;
    }
}
