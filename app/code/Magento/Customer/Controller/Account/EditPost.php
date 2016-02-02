<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Account;

use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerExtractor;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\State\UserLockedException;
use Magento\Customer\Helper\AccountManagement as AccountManagementHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EditPost extends \Magento\Customer\Controller\AbstractAccount
{
    /** @var AccountManagementInterface */
    protected $customerAccountManagement;

    /** @var CustomerRepositoryInterface  */
    protected $customerRepository;

    /** @var Validator */
    protected $formKeyValidator;

    /** @var CustomerExtractor */
    protected $customerExtractor;

    /**
     * @var Session
     */
    protected $session;

    /**
     * Account manager
     *
     * @var AccountManagementHelper
     */
    protected $accountManagementHelper;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param AccountManagementInterface $customerAccountManagement
     * @param CustomerRepositoryInterface $customerRepository
     * @param Validator $formKeyValidator
     * @param CustomerExtractor $customerExtractor
     * @param AccountManagementHelper $accountManagementHelper
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        AccountManagementInterface $customerAccountManagement,
        CustomerRepositoryInterface $customerRepository,
        Validator $formKeyValidator,
        CustomerExtractor $customerExtractor,
        AccountManagementHelper $accountManagementHelper
    ) {
        $this->session = $customerSession;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->customerRepository = $customerRepository;
        $this->formKeyValidator = $formKeyValidator;
        $this->customerExtractor = $customerExtractor;
        $this->accountManagementHelper = $accountManagementHelper;
        parent::__construct($context);
    }

    /**
     * Change customer email or password action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $resultRedirect->setPath('*/*/edit');
        }

        if ($this->getRequest()->isPost()) {
            $customerId = $this->session->getCustomerId();
            $currentCustomer = $this->customerRepository->getById($customerId);
            $currentCustomerEmail = $currentCustomer->getEmail();

            // Prepare new customer data
            $customer = $this->customerExtractor->extract('customer_account_edit', $this->_request);
            $customer->setId($customerId);
            if ($customer->getAddresses() == null) {
                $customer->setAddresses($currentCustomer->getAddresses());
            }

            try {
                $this->customerAccountManagement->checkLock($customer);
                // Check if a customer can change email
                if ($this->getRequest()->getParam('change_email')) {
                    if (!$this->isAllowedChangeCustomerEmail($customerId)) {
                        return $resultRedirect->setPath('*/*/edit');
                    }
                } else {
                    $customer->setEmail($currentCustomerEmail);
                }
                // Change customer password
                if ($this->getRequest()->getParam('change_password')) {
                    $this->changeCustomerPassword($currentCustomerEmail);
                }
                $this->customerRepository->save($customer);
            } catch (UserLockedException $e) {
                $this->session->logout();
                $this->session->start();
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('customer/account/login');
            } catch (AuthenticationException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (InputException $e) {
                $this->messageManager->addException($e, __('Invalid input'));
                foreach ($e->getErrors() as $error) {
                    $this->messageManager->addError($error->getMessage());
                }
            } catch (\Exception $e) {
                $message = __('We can\'t save the customer.')
                    . $e->getMessage()
                    . '<pre>' . $e->getTraceAsString() . '</pre>';
                $this->messageManager->addException($e, $message);
            }

            if ($this->messageManager->getMessages()->getCount() > 0) {
                $this->session->setCustomerFormData($this->getRequest()->getPostValue());
                return $resultRedirect->setPath('*/*/edit');
            }

            $this->messageManager->addSuccess(__('You saved the account information.'));
            return $resultRedirect->setPath('customer/account');
        }

        return $resultRedirect->setPath('*/*/edit');
    }

    /**
     * Is allowed to change customer email
     *
     * @param int $customerId
     * @return bool
     */
    protected function isAllowedChangeCustomerEmail($customerId)
    {
        return $this->customerAccountManagement->validatePasswordById(
                $customerId,
                $this->getRequest()->getPost('current_password')
            );
    }

    /**
     * Change customer password
     *
     * @param string $email
     * @return $this
     */
    protected function changeCustomerPassword($email)
    {
        $currPass = $this->getRequest()->getPost('current_password');
        $newPass = $this->getRequest()->getPost('password');
        $confPass = $this->getRequest()->getPost('password_confirmation');

        if (!strlen($newPass)) {
            $this->messageManager->addError(__('Please enter new password.'));
            return $this;
        }

        if ($newPass !== $confPass) {
            $this->messageManager->addError(__('Confirm your new password.'));
            return $this;
        }

        $this->customerAccountManagement->changePassword($email, $currPass, $newPass);

        return $this;
    }
}
