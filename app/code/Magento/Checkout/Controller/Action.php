<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Controller;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Controller for onepage checkouts
 * @since 2.0.0
 */
abstract class Action extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Customer\Model\Session
     * @since 2.0.0
     */
    protected $_customerSession;

    /**
     * @var CustomerRepositoryInterface
     * @since 2.0.0
     */
    protected $customerRepository;

    /**
     * @var AccountManagementInterface
     * @since 2.0.0
     */
    protected $accountManagement;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param AccountManagementInterface $accountManagement
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $accountManagement
    ) {
        $this->_customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->accountManagement = $accountManagement;
        parent::__construct($context);
    }

    /**
     * Make sure customer is valid, if logged in
     *
     * By default will add error messages and redirect to customer edit form
     *
     * @param bool $redirect - stop dispatch and redirect?
     * @param bool $addErrors - add error messages?
     * @return bool|\Magento\Framework\Controller\Result\Redirect
     * @since 2.0.0
     */
    protected function _preDispatchValidateCustomer($redirect = true, $addErrors = true)
    {
        try {
            $customer = $this->customerRepository->getById($this->_customerSession->getCustomerId());
        } catch (NoSuchEntityException $e) {
            return true;
        }

        if (isset($customer)) {
            $validationResult = $this->accountManagement->validate($customer);
            if (!$validationResult->isValid()) {
                if ($addErrors) {
                    foreach ($validationResult->getMessages() as $error) {
                        $this->messageManager->addError($error);
                    }
                }
                if ($redirect) {
                    $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
                    return $this->resultRedirectFactory->create()->setPath('customer/account/edit');
                }
                return false;
            }
        }
        return true;
    }
}
