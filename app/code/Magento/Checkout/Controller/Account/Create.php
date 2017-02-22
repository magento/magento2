<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Controller\Account;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\NoSuchEntityException;

class Create extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Sales\Api\OrderCustomerManagementInterface
     */
    protected $orderCustomerService;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Sales\Api\OrderCustomerManagementInterface $orderCustomerService
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Api\OrderCustomerManagementInterface $orderCustomerService
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->orderCustomerService = $orderCustomerService;
        parent::__construct($context);
    }

    /**
     * Execute request
     *
     * @throws AlreadyExistsException
     * @throws NoSuchEntityException
     * @throws \Exception
     * @return void
     */
    public function execute()
    {
        if ($this->customerSession->isLoggedIn()) {
            $this->messageManager->addError(__("Customer is already registered"));
            return;
        }
        $orderId = $this->checkoutSession->getLastOrderId();
        if (!$orderId) {
            $this->messageManager->addError(__("Your session has expired"));
            return;
        }
        try {
            $this->orderCustomerService->create($orderId);
        } catch (\Exception $e) {
            $this->messageManager->addException($e, $e->getMessage());
            throw $e;
        }
    }
}
