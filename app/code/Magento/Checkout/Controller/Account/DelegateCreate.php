<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Controller\Account;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Sales\Api\OrderCustomerDelegateInterface;

/**
 * Redirect guest customer for registration.
 */
class DelegateCreate implements HttpGetActionInterface
{
    /**
     * @var OrderCustomerDelegateInterface
     */
    private $delegateService;

    /**
     * @var CheckoutSession
     */
    private $session;

    /**
     * @var RedirectFactory
     */
    private $resultRedirectFactory;

    /**
     * @param OrderCustomerDelegateInterface $customerDelegation
     * @param CheckoutSession $session
     * @param RedirectFactory $resultRedirectFactory
     */
    public function __construct(
        OrderCustomerDelegateInterface $customerDelegation,
        CheckoutSession $session,
        RedirectFactory $resultRedirectFactory
    ) {
        $this->delegateService = $customerDelegation;
        $this->session = $session;
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var string|null $orderId */
        $orderId = $this->session->getLastOrderId();
        if (!$orderId) {
            return $this->resultRedirectFactory->create()->setPath('/');
        }

        return $this->delegateService->delegateNew((int)$orderId);
    }
}
