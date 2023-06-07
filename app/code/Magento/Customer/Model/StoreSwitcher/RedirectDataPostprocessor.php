<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\StoreSwitcher;

use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreSwitcher\ContextInterface;
use Magento\Store\Model\StoreSwitcher\RedirectDataPostprocessorInterface;
use Psr\Log\LoggerInterface;

/**
 * Process customer data redirected from origin store
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class RedirectDataPostprocessor implements RedirectDataPostprocessorInterface
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
    public function process(ContextInterface $context, array $data): void
    {
        if (!empty($data['customer_id'])) {
            try {
                $customer = $this->customerRegistry->retrieve($data['customer_id']);
                if (!$this->session->isLoggedIn()
                    && in_array($context->getTargetStore()->getId(), $customer->getSharedStoreIds())
                ) {
                    $this->session->setCustomerDataAsLoggedIn($customer->getDataModel());
                }
            } catch (NoSuchEntityException $e) {
                $this->logger->error($e);
                throw new LocalizedException(__('Something went wrong.'), $e);
            } catch (LocalizedException $e) {
                $this->logger->error($e);
                throw new LocalizedException(__('Something went wrong.'), $e);
            }
        }
    }
}
