<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Controller\Account;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\SessionCleanerInterface;
use Magento\Customer\Model\AccountConfirmation;
use Magento\Customer\Model\AddressRegistry;
use Magento\Customer\Model\Url;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Customer\Model\AuthenticationInterface;
use Magento\Customer\Model\Customer\Mapper;
use Magento\Customer\Model\EmailNotificationInterface;
use Magento\Customer\Model\Metadata\Form\File;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerExtractor;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\SessionException;
use Magento\Framework\Exception\State\UserLockedException;
use Magento\Customer\Controller\AbstractAccount;
use Magento\Framework\Phrase;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Customer edit account information controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 */
class EditPost extends AbstractAccount implements CsrfAwareActionInterface, HttpPostActionInterface
{
    /**
     * Form code for data extractor
     */
    public const FORM_DATA_EXTRACTOR_CODE = 'customer_account_edit';

    /**
     * @var AccountManagementInterface
     */
    protected $accountManagement;

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

    /**
     * @var EmailNotificationInterface
     */
    private $emailNotification;

    /**
     * @var AuthenticationInterface
     */
    private $authentication;

    /**
     * @var Mapper
     */
    private $customerMapper;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var AddressRegistry
     */
    private $addressRegistry;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var SessionCleanerInterface
     */
    private $sessionCleaner;

    /**
     * @var AccountConfirmation
     */
    private $accountConfirmation;

    /**
     * @var Url
     */
    private Url $customerUrl;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param AccountManagementInterface $accountManagement
     * @param CustomerRepositoryInterface $customerRepository
     * @param Validator $formKeyValidator
     * @param CustomerExtractor $customerExtractor
     * @param Escaper|null $escaper
     * @param AddressRegistry|null $addressRegistry
     * @param Filesystem|null $filesystem
     * @param SessionCleanerInterface|null $sessionCleaner
     * @param AccountConfirmation|null $accountConfirmation
     * @param Url|null $customerUrl
     * @param Mapper|null $customerMapper
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        AccountManagementInterface $accountManagement,
        CustomerRepositoryInterface $customerRepository,
        Validator $formKeyValidator,
        CustomerExtractor $customerExtractor,
        ?Escaper $escaper = null,
        ?AddressRegistry $addressRegistry = null,
        ?Filesystem $filesystem = null,
        ?SessionCleanerInterface $sessionCleaner = null,
        ?AccountConfirmation $accountConfirmation = null,
        ?Url $customerUrl = null,
        ?Mapper $customerMapper = null
    ) {
        parent::__construct($context);
        $this->session = $customerSession;
        $this->accountManagement = $accountManagement;
        $this->customerRepository = $customerRepository;
        $this->formKeyValidator = $formKeyValidator;
        $this->customerExtractor = $customerExtractor;
        $this->escaper = $escaper ?: ObjectManager::getInstance()->get(Escaper::class);
        $this->addressRegistry = $addressRegistry ?: ObjectManager::getInstance()->get(AddressRegistry::class);
        $this->filesystem = $filesystem ?: ObjectManager::getInstance()->get(Filesystem::class);
        $this->sessionCleaner = $sessionCleaner ?: ObjectManager::getInstance()->get(SessionCleanerInterface::class);
        $this->accountConfirmation = $accountConfirmation ?: ObjectManager::getInstance()
            ->get(AccountConfirmation::class);
        $this->customerUrl = $customerUrl ?: ObjectManager::getInstance()->get(Url::class);
        $this->customerMapper = $customerMapper ?: ObjectManager::getInstance()->get(Mapper::class);
    }

    /**
     * Get authentication
     *
     * @return AuthenticationInterface
     */
    private function getAuthentication()
    {

        if (!($this->authentication instanceof AuthenticationInterface)) {
            return ObjectManager::getInstance()->get(AuthenticationInterface::class);
        } else {
            return $this->authentication;
        }
    }

    /**
     * Get email notification
     *
     * @return EmailNotificationInterface
     */
    private function getEmailNotification()
    {
        if (!($this->emailNotification instanceof EmailNotificationInterface)) {
            return ObjectManager::getInstance()->get(EmailNotificationInterface::class);
        } else {
            return $this->emailNotification;
        }
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('*/*/edit');

        return new InvalidRequestException(
            $resultRedirect,
            [new Phrase('Invalid Form Key. Please refresh the page.')]
        );
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return null;
    }

    /**
     * Change customer email or password action
     *
     * @return Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @throws SessionException
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $validFormKey = $this->formKeyValidator->validate($this->getRequest());

        if ($validFormKey && $this->getRequest()->isPost()) {
            $customer = $this->getCustomerDataObject($this->session->getCustomerId());
            $customerCandidate = $this->populateNewCustomerDataObject($this->_request, $customer);

            try {
                // whether a customer enabled change email option
                $isEmailChanged = $this->processChangeEmailRequest($customer);

                // whether a customer enabled change password option
                $isPasswordChanged = $this->changeCustomerPassword($customer->getEmail());

                // No need to validate customer address while editing customer profile
                $this->disableAddressValidation($customerCandidate);

                $this->customerRepository->save($customerCandidate);
                $updatedCustomer = $this->customerRepository->getById($customerCandidate->getId());

                $this->getEmailNotification()->credentialsChanged(
                    $updatedCustomer,
                    $customer->getEmail(),
                    $isPasswordChanged
                );

                $this->dispatchSuccessEvent($updatedCustomer);
                $this->messageManager->addSuccessMessage(__('You saved the account information.'));
                // logout from current session if password or email changed.
                if ($isPasswordChanged || $isEmailChanged) {
                    $this->session->logout();
                    $this->session->start();
                    $this->addComplexSuccessMessage($customer, $updatedCustomer);

                    return $resultRedirect->setPath('customer/account/login');
                }
                return $resultRedirect->setPath('customer/account');
            } catch (InvalidEmailOrPasswordException $e) {
                $this->messageManager->addErrorMessage($this->escaper->escapeHtml($e->getMessage()));
            } catch (UserLockedException $e) {
                $message = __(
                    'The account sign-in was incorrect or your account is disabled temporarily. '
                    . 'Please wait and try again later.'
                );
                $this->session->logout();
                $this->session->start();
                $this->messageManager->addErrorMessage($message);

                return $resultRedirect->setPath('customer/account/login');
            } catch (InputException $e) {
                $this->messageManager->addErrorMessage($this->escaper->escapeHtml($e->getMessage()));
                foreach ($e->getErrors() as $error) {
                    $this->messageManager->addErrorMessage($this->escaper->escapeHtml($error->getMessage()));
                }
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('We can\'t save the customer.'));
            }

            $this->session->setCustomerFormData($this->getRequest()->getPostValue());
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('*/*/edit');

        return $resultRedirect;
    }

    /**
     * Adds a complex success message if email confirmation is required
     *
     * @param CustomerInterface $outdatedCustomer
     * @param CustomerInterface $updatedCustomer
     * @throws LocalizedException
     */
    private function addComplexSuccessMessage(
        CustomerInterface $outdatedCustomer,
        CustomerInterface $updatedCustomer
    ): void {
        if (($outdatedCustomer->getEmail() !== $updatedCustomer->getEmail())
            && $this->accountConfirmation->isCustomerEmailChangedConfirmRequired($updatedCustomer)) {
            $this->messageManager->addComplexSuccessMessage(
                'confirmAccountSuccessMessage',
                ['url' => $this->customerUrl->getEmailConfirmationUrl($updatedCustomer->getEmail())]
            );
        }
    }

    /**
     * Account editing action completed successfully event
     *
     * @param CustomerInterface $customerCandidateDataObject
     * @return void
     */
    private function dispatchSuccessEvent(CustomerInterface $customerCandidateDataObject)
    {
        $this->_eventManager->dispatch(
            'customer_account_edited',
            ['email' => $customerCandidateDataObject->getEmail()]
        );
    }

    /**
     * Get customer data object
     *
     * @param int $customerId
     *
     * @return CustomerInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getCustomerDataObject($customerId)
    {
        return $this->customerRepository->getById($customerId);
    }

    /**
     * Create Data Transfer Object of customer candidate
     *
     * @param RequestInterface $inputData
     * @param CustomerInterface $currentCustomerData
     * @return CustomerInterface
     */
    private function populateNewCustomerDataObject(
        RequestInterface $inputData,
        CustomerInterface $currentCustomerData
    ) {
        $attributeValues = $this->getCustomerMapper()->toFlatArray($currentCustomerData);
        $customerDto = $this->customerExtractor->extract(
            self::FORM_DATA_EXTRACTOR_CODE,
            $inputData,
            $attributeValues
        );
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
     * @return boolean
     * @throws InvalidEmailOrPasswordException|InputException|LocalizedException
     */
    protected function changeCustomerPassword($email)
    {
        $isPasswordChanged = false;
        if ($this->getRequest()->getParam('change_password')) {
            $currPass = $this->getRequest()->getPost('current_password');
            $newPass = $this->getRequest()->getPost('password');
            $confPass = $this->getRequest()->getPost('password_confirmation');
            if ($newPass != $confPass) {
                throw new InputException(__('Password confirmation doesn\'t match entered password.'));
            }

            $isPasswordChanged = $this->accountManagement->changePassword($email, $currPass, $newPass);
        }

        return $isPasswordChanged;
    }

    /**
     * Process change email request
     *
     * @param CustomerInterface $currentCustomerDataObject
     * @return bool
     * @throws InvalidEmailOrPasswordException
     * @throws UserLockedException
     */
    private function processChangeEmailRequest(CustomerInterface $currentCustomerDataObject)
    {
        if ($this->getRequest()->getParam('change_email')) {
            // authenticate user for changing email
            try {
                $this->getAuthentication()->authenticate(
                    $currentCustomerDataObject->getId(),
                    $this->getRequest()->getPost('current_password')
                );
                $this->sessionCleaner->clearFor((int) $currentCustomerDataObject->getId());
                return true;
            } catch (InvalidEmailOrPasswordException $e) {
                throw new InvalidEmailOrPasswordException(
                    __("The password doesn't match this account. Verify the password and try again.")
                );
            }
        }
        return false;
    }

    /**
     * Get Customer Mapper instance
     *
     * @return Mapper
     */
    private function getCustomerMapper()
    {
        if ($this->customerMapper === null) {
            $this->customerMapper = ObjectManager::getInstance()->get(Mapper::class);
        }
        return $this->customerMapper;
    }

    /**
     * Disable Customer Address Validation
     *
     * @param CustomerInterface $customer
     * @throws NoSuchEntityException
     */
    private function disableAddressValidation($customer)
    {
        foreach ($customer->getAddresses() as $address) {
            $addressModel = $this->addressRegistry->retrieve($address->getId());
            $addressModel->setShouldIgnoreValidation(true);
        }
    }
}
