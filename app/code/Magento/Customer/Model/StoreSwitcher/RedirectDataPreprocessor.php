<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\StoreSwitcher;

use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Session;
use Magento\Store\Model\StoreSwitcher\ContextInterface;
use Magento\Store\Model\StoreSwitcher\RedirectDataPreprocessorInterface;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Collect customer data to be redirected to target store
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class RedirectDataPreprocessor implements RedirectDataPreprocessorInterface
{
    /**
     * @var Session
     */
    private $session;
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
     * @param Session $session
     * @param LoggerInterface $logger
     */
    public function __construct(
        CustomerRegistry $customerRegistry,
        Session $session,
        LoggerInterface $logger
    ) {
        $this->customerRegistry = $customerRegistry;
        $this->session = $session;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function process(ContextInterface $context, array $data): array
    {
        if ($this->session->isLoggedIn()) {
            try {
                $customer = $this->customerRegistry->retrieve($this->session->getCustomerId());
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
