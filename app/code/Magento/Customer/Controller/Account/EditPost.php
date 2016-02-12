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
use Magento\Framework\Exception\InputException;
use Magento\Customer\Helper\EmailNotification;
use Magento\Customer\Helper\AccountManagement;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Exception\State\UserLockedException;

/**
 * Class EditPost
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EditPost extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * Form code for data extractor
     */
    const FORM_DATA_EXTRACTOR_CODE = 'customer_account_edit';

    /**
     * @var AccountManagementInterface
     */
    protected $customerAccountManagement;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * @var CustomerExtractor
     */
    protected $customerExtractor;

    /**
     * @var Session
     */
    protected $session;

    /** @var EmailNotification */
    protected $emailNotification;

    /**
     * @var AccountManagement
     */
    protected $accountManagementHelper;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param AccountManagementInterface $customerAccountManagement
     * @param CustomerRepositoryInterface $customerRepository
     * @param Validator $formKeyValidator
     * @param CustomerExtractor $customerExtractor
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        AccountManagementInterface $customerAccountManagement,
        CustomerRepositoryInterface $customerRepository,
        Validator $formKeyValidator,
        CustomerExtractor $customerExtractor
    ) {
        parent::__construct($context);
        $this->session = $customerSession;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->customerRepository = $customerRepository;
        $this->formKeyValidator = $formKeyValidator;
        $this->customerExtractor = $customerExtractor;
    }

    /**
     * Set account management helper
     *
     * @param AccountManagement $accountManagementHelper
     * @return void
     * @deprecated
     */
    public function setAccountManagementHelper(AccountManagement $accountManagementHelper)
    {

        $this->accountManagementHelper = $accountManagementHelper;
    }

    /**
     * Get account management helper
     *
     * @return AccountManagement
     * @deprecated
     */
    public function getAccountManagementHelper()
    {

        if (!($this->accountManagementHelper instanceof \Magento\Customer\Helper\AccountManagement)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(
                'Magento\Customer\Helper\AccountManagement'
            );
        } else {
            return $this->accountManagementHelper;
        }
    }

    /**
     * Set email notification
     *
     * @param \Magento\Customer\Helper\EmailNotification $emailNotification
     * @return void
     * @deprecated
     */
    public function setEmailNotification(\Magento\Customer\Helper\EmailNotification $emailNotification)
    {

        $this->emailNotification = $emailNotification;
    }

    /**
     * Get email notification
     *
     * @return \Magento\Customer\Helper\EmailNotification
     * @deprecated
     */
    public function getEmailNotification()
    {

        if (!($this->emailNotification instanceof \Magento\Customer\Helper\EmailNotification)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Customer\Helper\EmailNotification');
        } else {
            return $this->emailNotification;
        }
    }

    /**
     * Change customer email or password action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $resultRedirect->setPath('*/*/edit');
        }

        if ($this->getRequest()->isPost()) {
            $currentCustomerDataObject = $this->getCurrentCustomerDataObject();
            $customerCandidateDataObject = $this->populateNewCustomerDataObject(
                $this->_request,
                $currentCustomerDataObject
            );

            try {
                // whether a customer enabled change email option
                $this->changeEmail($currentCustomerDataObject);

                // whether a customer enabled change password option
                $isPasswordChanged = $this->changePassword($currentCustomerDataObject);

                $this->customerRepository->save($customerCandidateDataObject);
                $this->getEmailNotification()->sendNotificationEmailsIfRequired(
                    $currentCustomerDataObject,
                    $customerCandidateDataObject,
                    $isPasswordChanged
                );
                $this->dispatchSuccessEvent($customerCandidateDataObject);
                $this->messageManager->addSuccess(__('You saved the account information.'));
                return $resultRedirect->setPath('customer/account');
            } catch (InvalidEmailOrPasswordException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (UserLockedException $e) {
                $this->session->logout();
                $this->session->start();
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('customer/account/login');
            } catch (InputException $e) {
                $this->messageManager->addError($e->getMessage());
                foreach ($e->getErrors() as $error) {
                    $this->messageManager->addError($error->getMessage());
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('We can\'t save the customer.'));
            }

            $this->session->setCustomerFormData($this->getRequest()->getPostValue());
            return $resultRedirect->setPath('*/*/edit');
        }

        return $resultRedirect->setPath('*/*/edit');
    }

    /**
     * Change email if required
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $currentCustomerDataObject
     * @return $this
     * @throws InvalidEmailOrPasswordException
     */
    protected function changeEmail(\Magento\Customer\Api\Data\CustomerInterface $currentCustomerDataObject)
    {
        if ($this->getRequest()->getParam('change_email')) {
            $this->getAccountManagementHelper()->validatePasswordAndLockStatus(
                $currentCustomerDataObject,
                $this->getRequest()->getPost('current_password')
            );
        }

        return $this;
    }

    /**
     * Change password if required
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $currentCustomerDataObject
     * @return bool
     * @throws InputException
     */
    protected function changePassword(\Magento\Customer\Api\Data\CustomerInterface $currentCustomerDataObject)
    {
        $isPasswordChanged = false;
        if ($this->getRequest()->getParam('change_password')) {
            $isPasswordChanged = $this->changeCustomerPassword(
                $currentCustomerDataObject->getEmail()
            );
        }

        return $isPasswordChanged;
    }

    /**
     * Account editing action completed successfully event
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customerCandidateDataObject
     * @return void
     */
    protected function dispatchSuccessEvent(\Magento\Customer\Api\Data\CustomerInterface $customerCandidateDataObject)
    {
        $this->_eventManager->dispatch(
            'customer_account_edited',
            ['email' => $customerCandidateDataObject->getEmail()]
        );
    }

    /**
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    protected function getCurrentCustomerDataObject()
    {
        return $this->customerRepository->getById(
            $this->session->getCustomerId()
        );
    }

    /**
     * Create Data Transfer Object of customer candidate
     *
     * @param \Magento\Framework\App\RequestInterface $inputData
     * @param \Magento\Customer\Api\Data\CustomerInterface $currentCustomerData
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    protected function populateNewCustomerDataObject(
        \Magento\Framework\App\RequestInterface $inputData,
        \Magento\Customer\Api\Data\CustomerInterface $currentCustomerData
    ) {
        $customerDto = $this->customerExtractor->extract(self::FORM_DATA_EXTRACTOR_CODE, $inputData);
        $customerDto->setId($currentCustomerData->getId());
        if (!$customerDto->getAddresses()) {
            $customerDto->setAddresses($currentCustomerData->getAddresses());
        }
        if (!$inputData->getParam('change_email')) {
            $customerDto->setEmail($currentCustomerData->getEmail());
        }

        return $customerDto;
    }

    /**
     * Change customer password
     *
     * @param string $email
     * @return bool
     * @throws InvalidEmailOrPasswordException|InputException
     */
    protected function changeCustomerPassword($email)
    {
        $currPass = $this->getRequest()->getPost('current_password');
        $newPass = $this->getRequest()->getPost('password');
        $confPass = $this->getRequest()->getPost('password_confirmation');
        if ($newPass != $confPass) {
            throw new InputException(__('Password confirmation doesn\'t match entered password.'));
        }

        return $this->customerAccountManagement->changePassword($email, $currPass, $newPass);
    }
}
