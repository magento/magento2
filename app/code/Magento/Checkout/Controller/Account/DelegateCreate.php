<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
=======

>>>>>>> upstream/2.2-develop
declare(strict_types=1);

namespace Magento\Checkout\Controller\Account;

<<<<<<< HEAD
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
=======
>>>>>>> upstream/2.2-develop
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session;
use Magento\Sales\Api\OrderCustomerDelegateInterface;

/**
 * Redirect guest customer for registration.
 */
<<<<<<< HEAD
class DelegateCreate extends Action implements HttpGetActionInterface
=======
class DelegateCreate extends Action
>>>>>>> upstream/2.2-develop
{
    /**
     * @var OrderCustomerDelegateInterface
     */
    private $delegateService;

    /**
     * @var Session
     */
    private $session;

    /**
     * @param Context $context
     * @param OrderCustomerDelegateInterface $customerDelegation
     * @param Session $session
     */
    public function __construct(
        Context $context,
        OrderCustomerDelegateInterface $customerDelegation,
        Session $session
    ) {
        parent::__construct($context);
        $this->delegateService = $customerDelegation;
        $this->session = $session;
    }

    /**
<<<<<<< HEAD
     * {@inheritdoc}
=======
     * @inheritDoc
>>>>>>> upstream/2.2-develop
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
