<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Newsletter\Controller\Manage;

use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Newsletter\Model\Subscriber;

/**
 * Customers newsletter subscription save controller
 */
class Save extends \Magento\Newsletter\Controller\Manage implements HttpPostActionInterface, HttpGetActionInterface
{
    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $formKeyValidator;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * @var \Magento\Newsletter\Model\SubscriberFactory
     */
    protected $subscriberFactory;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param CustomerRepository $customerRepository
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        CustomerRepository $customerRepository,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
    ) {
        $this->storeManager = $storeManager;
        $this->formKeyValidator = $formKeyValidator;
        $this->customerRepository = $customerRepository;
        $this->subscriberFactory = $subscriberFactory;
        parent::__construct($context, $customerSession);
    }

    /**
     * Save newsletter subscription preference action
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $this->_redirect('customer/account/');
        }

        $customerId = $this->_customerSession->getCustomerId();
        if ($customerId === null) {
            $this->messageManager->addError(__('Something went wrong while saving your subscription.'));
        } else {
            try {
                $customer = $this->customerRepository->getById($customerId);
                $storeId = $this->storeManager->getStore()->getId();
                $customer->setStoreId($storeId);
                $isSubscribedState = $customer->getExtensionAttributes()
                    ->getIsSubscribed();
                $isSubscribedParam = (boolean)$this->getRequest()
                    ->getParam('is_subscribed', false);
                if ($isSubscribedParam !== $isSubscribedState) {
                    // No need to validate customer and customer address while saving subscription preferences
                    $this->setIgnoreValidationFlag($customer);
                    $this->customerRepository->save($customer);
                    if ($isSubscribedParam) {
                        $subscribeModel = $this->subscriberFactory->create()
                            ->subscribeCustomerById($customerId);
                        $subscribeStatus = $subscribeModel->getStatus();
                        if ($subscribeStatus == Subscriber::STATUS_SUBSCRIBED) {
                            $this->messageManager->addSuccess(__('We have saved your subscription.'));
                        } else {
                            $this->messageManager->addSuccess(__('A confirmation request has been sent.'));
                        }
                    } else {
                        $this->subscriberFactory->create()
                            ->unsubscribeCustomerById($customerId);
                        $this->messageManager->addSuccess(__('We have removed your newsletter subscription.'));
                    }
                } else {
                    $this->messageManager->addSuccess(__('We have updated your subscription.'));
                }
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Something went wrong while saving your subscription.'));
            }
        }
        return $this->_redirect('customer/account/');
    }

    /**
     * Set ignore_validation_flag to skip unnecessary address and customer validation
     *
     * @param CustomerInterface $customer
     * @return void
     */
    private function setIgnoreValidationFlag(CustomerInterface $customer): void
    {
        $customer->setData('ignore_validation_flag', true);
    }
}
