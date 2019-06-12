<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD

=======
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
declare(strict_types=1);

namespace Magento\Checkout\Controller\Account;

<<<<<<< HEAD
=======
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
