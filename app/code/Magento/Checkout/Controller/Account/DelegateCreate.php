<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD

=======
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
declare(strict_types=1);

namespace Magento\Checkout\Controller\Account;

<<<<<<< HEAD
=======
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session;
use Magento\Sales\Api\OrderCustomerDelegateInterface;

/**
 * Redirect guest customer for registration.
 */
<<<<<<< HEAD
class DelegateCreate extends Action
=======
class DelegateCreate extends Action implements HttpGetActionInterface
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
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
     * @inheritDoc
=======
     * {@inheritdoc}
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
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
