<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Helper\View as CustomerViewHelper;
use Magento\Customer\Model\Config\Share as ConfigShare;
use Magento\Customer\Model\Customer as CustomerModel;
use Magento\Customer\Model\Metadata\Validator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface as Encryptor;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\ExpiredException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Psr\Log\LoggerInterface as Logger;
use Magento\Framework\Mail\Exception as MailException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Math\Random;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\String as StringHelper;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Handle various customer account actions
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class AccountManagement implements AccountManagementInterface
{
    /**
     * Configuration paths for email templates and identities
     */
    const XML_PATH_REGISTER_EMAIL_TEMPLATE = 'customer/create_account/email_template';

    const XML_PATH_REGISTER_EMAIL_IDENTITY = 'customer/create_account/email_identity';

    const XML_PATH_REMIND_EMAIL_TEMPLATE = 'customer/password/remind_email_template';

    const XML_PATH_FORGOT_EMAIL_TEMPLATE = 'customer/password/forgot_email_template';

    const XML_PATH_FORGOT_EMAIL_IDENTITY = 'customer/password/forgot_email_identity';

    const XML_PATH_RESET_PASSWORD_TEMPLATE = 'customer/password/reset_password_template';

    const XML_PATH_IS_CONFIRM = 'customer/create_account/confirm';

    const XML_PATH_CONFIRM_EMAIL_TEMPLATE = 'customer/create_account/email_confirmation_template';

    const XML_PATH_CONFIRMED_EMAIL_TEMPLATE = 'customer/create_account/email_confirmed_template';

    /**
     * Codes of exceptions related to customer model
     */
    const EXCEPTION_EMAIL_EXISTS = 3;

    // Constants for the type of new account email to be sent
    const NEW_ACCOUNT_EMAIL_REGISTERED = 'registered';

    // welcome email, when confirmation is enabled
    const NEW_ACCOUNT_EMAIL_CONFIRMATION = 'confirmation';

    /**
     * Constants for types of emails to send out.
     * pdl:
     * forgot, remind, reset email templates
     */
    const EMAIL_REMINDER = 'email_reminder';

    const EMAIL_RESET = 'email_reset';

    const MIN_PASSWORD_LENGTH = 6;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var \Magento\Customer\Api\Data\CustomerDataBuilder
     */
    private $customerDataBuilder;

    /**
     * @var \Magento\Customer\Api\Data\ValidationResultsDataBuilder
     */
    private $validationResultsDataBuilder;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Random
     */
    private $mathRandom;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var CustomerMetadataInterface
     */
    private $customerMetadataService;

    /**
     * @var \Magento\Framework\Url
     */
    private $url;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Encryptor
     */
    private $encryptor;

    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var ConfigShare
     */
    private $configShare;

    /**
     * @var StringHelper
     */
    private $stringHelper;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var DataObjectProcessor
     */
    protected $dataProcessor;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var CustomerViewHelper
     */
    protected $customerViewHelper;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var \Magento\Framework\ObjectFactory
     */
    protected $objectFactory;

    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;

    /**
     * @var CustomerModel
     */
    protected $customerModel;

    /**
     * @param CustomerFactory $customerFactory
     * @param ManagerInterface $eventManager
     * @param StoreManagerInterface $storeManager
     * @param Random $mathRandom
     * @param Validator $validator
     * @param \Magento\Customer\Api\Data\ValidationResultsDataBuilder $validationResultsDataBuilder
     * @param AddressRepositoryInterface $addressRepository
     * @param CustomerMetadataInterface $customerMetadataService
     * @param CustomerRegistry $customerRegistry
     * @param \Magento\Framework\Url $url
     * @param Logger $logger
     * @param Encryptor $encryptor
     * @param ConfigShare $configShare
     * @param StringHelper $stringHelper
     * @param CustomerRepositoryInterface $customerRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param TransportBuilder $transportBuilder
     * @param \Magento\Customer\Api\Data\CustomerDataBuilder $customerDataBuilder
     * @param DataObjectProcessor $dataProcessor
     * @param \Magento\Framework\Registry $registry
     * @param CustomerViewHelper $customerViewHelper
     * @param DateTime $dateTime
     * @param CustomerModel $customerModel
     * @param \Magento\Framework\ObjectFactory $objectFactory
     * @param \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        CustomerFactory $customerFactory,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        Random $mathRandom,
        Validator $validator,
        \Magento\Customer\Api\Data\ValidationResultsDataBuilder $validationResultsDataBuilder,
        AddressRepositoryInterface $addressRepository,
        CustomerMetadataInterface $customerMetadataService,
        CustomerRegistry $customerRegistry,
        \Magento\Framework\Url $url,
        Logger $logger,
        Encryptor $encryptor,
        ConfigShare $configShare,
        StringHelper $stringHelper,
        CustomerRepositoryInterface $customerRepository,
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder,
        \Magento\Customer\Api\Data\CustomerDataBuilder $customerDataBuilder,
        DataObjectProcessor $dataProcessor,
        \Magento\Framework\Registry $registry,
        CustomerViewHelper $customerViewHelper,
        DateTime $dateTime,
        CustomerModel $customerModel,
        \Magento\Framework\ObjectFactory $objectFactory,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->customerFactory = $customerFactory;
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
        $this->mathRandom = $mathRandom;
        $this->validator = $validator;
        $this->validationResultsDataBuilder = $validationResultsDataBuilder;
        $this->addressRepository = $addressRepository;
        $this->customerMetadataService = $customerMetadataService;
        $this->customerRegistry = $customerRegistry;
        $this->url = $url;
        $this->logger = $logger;
        $this->encryptor = $encryptor;
        $this->configShare = $configShare;
        $this->stringHelper = $stringHelper;
        $this->customerRepository = $customerRepository;
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->customerDataBuilder = $customerDataBuilder;
        $this->dataProcessor = $dataProcessor;
        $this->registry = $registry;
        $this->customerViewHelper = $customerViewHelper;
        $this->dateTime = $dateTime;
        $this->customerModel = $customerModel;
        $this->objectFactory = $objectFactory;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function resendConfirmation($email, $websiteId = null, $redirectUrl = '')
    {
        $customer = $this->customerRepository->get($email, $websiteId);
        if (!$customer->getConfirmation()) {
            throw new InvalidTransitionException('No confirmation needed.');
        }

        try {
            $this->sendNewAccountEmail(
                $customer,
                self::NEW_ACCOUNT_EMAIL_CONFIRMATION,
                $redirectUrl,
                $this->storeManager->getStore()->getId()
            );
        } catch (MailException $e) {
            // If we are not able to send a new account email, this should be ignored
            $this->logger->critical($e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function activate($email, $confirmationKey)
    {
        $customer = $this->customerRepository->get($email);
        return $this->activateCustomer($customer, $confirmationKey);
    }

    /**
     * {@inheritdoc}
     */
    public function activateById($customerId, $confirmationKey)
    {
        $customer = $this->customerRepository->getById($customerId);
        return $this->activateCustomer($customer, $confirmationKey);
    }

    /**
     * Activate a customer account using a key that was sent in a confirmation e-mail.
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param string $confirmationKey
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    private function activateCustomer($customer, $confirmationKey)
    {
        // check if customer is inactive
        if (!$customer->getConfirmation()) {
            throw new InvalidTransitionException('Account already active');
        }

        if ($customer->getConfirmation() !== $confirmationKey) {
            throw new InputMismatchException('Invalid confirmation token');
        }

        $customer = $this->dataProcessor
            ->buildOutputDataArray($customer, '\Magento\Customer\Api\Data\CustomerInterface');

        $customer = $this->customerDataBuilder->populateWithArray($customer)->setConfirmation(null)->create();
        $this->customerRepository->save($customer);
        $this->sendNewAccountEmail($customer, 'confirmed', '', $this->storeManager->getStore()->getId());
        return $customer;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate($username, $password)
    {
        $this->checkPasswordStrength($password);

        try {
            $customer = $this->customerRepository->get($username);
        } catch (NoSuchEntityException $e) {
            throw new InvalidEmailOrPasswordException('Invalid login or password.', []);
        }

        $hash = $this->customerRegistry->retrieveSecureData($customer->getId())->getPasswordHash();
        if (!$this->encryptor->validateHash($password, $hash)) {
            throw new InvalidEmailOrPasswordException('Invalid login or password.', []);
        }

        if ($customer->getConfirmation() && $this->isConfirmationRequired($customer)) {
            throw new EmailNotConfirmedException('This account is not confirmed.', []);
        }

        $this->eventManager->dispatch(
            'customer_customer_authenticated',
            ['model' => $this->getFullCustomerObject($customer), 'password' => $password]
        );

        $this->eventManager->dispatch('customer_data_object_login', ['customer' => $customer]);

        return $customer;
    }

    /**
     * {@inheritdoc}
     */
    public function validateResetPasswordLinkToken($customerId, $resetPasswordLinkToken)
    {
        $this->validateResetPasswordToken($customerId, $resetPasswordLinkToken);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function initiatePasswordReset($email, $template, $websiteId = null)
    {
        if (is_null($websiteId)) {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
        }
        // load customer by email
        $customer = $this->customerRepository->get($email, $websiteId);

        $newPasswordToken = $this->mathRandom->getUniqueHash();
        $this->changeResetPasswordLinkToken($customer, $newPasswordToken);

        try {
            switch ($template) {
                case AccountManagement::EMAIL_REMINDER:
                    $this->sendPasswordReminderEmail($customer, $newPasswordToken);
                    break;
                case AccountManagement::EMAIL_RESET:
                    $this->sendPasswordResetConfirmationEmail($customer);
                    break;
                default:
                    throw new InputException(
                        InputException::INVALID_FIELD_VALUE,
                        ['value' => $template, 'fieldName' => 'email type']
                    );
            }
        } catch (MailException $e) {
            // If we are not able to send a reset password email, this should be ignored
            $this->logger->critical($e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function resetPassword($email, $resetToken, $newPassword)
    {
        $customer = $this->customerRepository->get($email);
        //Validate Token and new password strength
        $this->validateResetPasswordToken($customer->getId(), $resetToken);
        $this->checkPasswordStrength($newPassword);
        //Update secure data
        $customerSecure = $this->customerRegistry->retrieveSecureData($customer->getId());
        $customerSecure->setRpToken(null);
        $customerSecure->setRpTokenCreatedAt(null);
        $customerSecure->setPasswordHash($this->createPasswordHash($newPassword));
        $this->customerRepository->save($customer);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfirmationStatus($customerId)
    {
        // load customer by id
        $customer = $this->customerRepository->getById($customerId);
        if (!$customer->getConfirmation()) {
            return self::ACCOUNT_CONFIRMED;
        }
        if ($this->isConfirmationRequired($customer)) {
            return self::ACCOUNT_CONFIRMATION_REQUIRED;
        }
        return self::ACCOUNT_CONFIRMATION_NOT_REQUIRED;
    }

    /**
     * {@inheritdoc}
     */
    public function createAccount(
        CustomerInterface $customer,
        $password = null,
        $redirectUrl = ''
    ) {
        if (!is_null($password)) {
            $this->checkPasswordStrength($password);
        } else {
            $password = $this->mathRandom->getRandomString(self::MIN_PASSWORD_LENGTH);
        }
        $hash = $this->createPasswordHash($password);
        return $this->createAccountWithPasswordHash($customer, $hash, $redirectUrl);
    }

    /**
     * {@inheritdoc}
     */
    public function createAccountWithPasswordHash(
        CustomerInterface $customer,
        $hash,
        $redirectUrl = ''
    ) {
        // This logic allows an existing customer to be added to a different store.  No new account is created.
        // The plan is to move this logic into a new method called something like 'registerAccountWithStore'
        if ($customer->getId()) {
            $customer = $this->customerRepository->get($customer->getEmail());
            $websiteId = $customer->getWebsiteId();

            if ($this->isCustomerInStore($websiteId, $customer->getStoreId())) {
                throw new InputException('Customer already exists in this store.');
            }
            // Existing password hash will be used from secured customer data registry when saving customer
        }
        // Make sure we have a storeId to associate this customer with.
        if (!$customer->getStoreId()) {
            if ($customer->getWebsiteId()) {
                $storeId = $this->storeManager->getWebsite($customer->getWebsiteId())->getDefaultStore()->getId();
            } else {
                $storeId = $this->storeManager->getStore()->getId();
            }

            $customer = $this->customerDataBuilder->populate($customer)
                ->setStoreId($storeId)
                ->create();
        }

        try {
            // If customer exists existing hash will be used by Repository
            $customer = $this->customerRepository->save($customer, $hash);
        } catch (\Magento\Customer\Exception $e) {
            if ($e->getCode() === CustomerModel::EXCEPTION_EMAIL_EXISTS) {
                throw new InputMismatchException('Customer with the same email already exists in associated website.');
            }
            throw $e;
        }

        foreach ($customer->getAddresses() as $address) {
            $this->addressRepository->save($address);
        }
        $customer = $this->customerRepository->getById($customer->getId());
        $newLinkToken = $this->mathRandom->getUniqueHash();
        $this->changeResetPasswordLinkToken($customer, $newLinkToken);
        $this->sendEmailConfirmation($customer, $redirectUrl);

        return $customer;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultBillingAddress($customerId)
    {
        $customer = $this->customerRepository->getById($customerId);
        return $this->getAddressById($customer, $customer->getDefaultBilling());
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultShippingAddress($customerId)
    {
        $customer = $this->customerRepository->getById($customerId);
        return $this->getAddressById($customer, $customer->getDefaultShipping());
    }

    /**
     * Send either confirmation or welcome email after an account creation
     *
     * @param CustomerInterface $customer
     * @param string $redirectUrl
     * @return void
     */
    protected function sendEmailConfirmation(CustomerInterface $customer, $redirectUrl)
    {
        try {
            if ($this->isConfirmationRequired($customer)) {
                $this->sendNewAccountEmail(
                    $customer,
                    self::NEW_ACCOUNT_EMAIL_CONFIRMATION,
                    $redirectUrl,
                    $customer->getStoreId()
                );
            } else {
                $this->sendNewAccountEmail(
                    $customer,
                    self::NEW_ACCOUNT_EMAIL_REGISTERED,
                    $redirectUrl,
                    $customer->getStoreId()
                );
            }
        } catch (MailException $e) {
            // If we are not able to send a new account email, this should be ignored
            $this->logger->critical($e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function changePassword($email, $currentPassword, $newPassword)
    {
        try {
            $customer = $this->customerRepository->get($email);
        } catch (NoSuchEntityException $e) {
            throw new InvalidEmailOrPasswordException('Invalid login or password.');
        }
        return $this->changePasswordForCustomer($customer, $currentPassword, $newPassword);
    }

    /**
     * {@inheritdoc}
     */
    public function changePasswordById($customerId, $currentPassword, $newPassword)
    {
        try {
            $customer = $this->customerRepository->getById($customerId);
        } catch (NoSuchEntityException $e) {
            throw new InvalidEmailOrPasswordException('Invalid login or password.');
        }
        return $this->changePasswordForCustomer($customer, $currentPassword, $newPassword);
    }

    /**
     * Change customer password.
     *
     * @param string $email
     * @param string $currentPassword
     * @param string $newPassword
     * @return bool true on success
     */
    private function changePasswordForCustomer($customer, $currentPassword, $newPassword)
    {
        $customerSecure = $this->customerRegistry->retrieveSecureData($customer->getId());
        $hash = $customerSecure->getPasswordHash();
        if (!$this->encryptor->validateHash($currentPassword, $hash)) {
            throw new InvalidEmailOrPasswordException("Password doesn't match for this account.", []);
        }
        $customerSecure->setRpToken(null);
        $customerSecure->setRpTokenCreatedAt(null);
        $this->checkPasswordStrength($newPassword);
        $customerSecure->setPasswordHash($this->createPasswordHash($newPassword));
        $this->customerRepository->save($customer);
        // FIXME: Are we using the proper template here?
        try {
            $this->sendPasswordResetNotificationEmail($customer);
        } catch (MailException $e) {
            $this->logger->critical($e);
        }

        return true;
    }

    /**
     * Make sure that password complies with minimum security requirements.
     *
     * @param string $password
     * @return void
     * @throws InputException
     */
    protected function checkPasswordStrength($password)
    {
        $length = $this->stringHelper->strlen($password);
        if ($length < self::MIN_PASSWORD_LENGTH) {
            throw new InputException(
                'The password must have at least %min_length characters.',
                ['min_length' => self::MIN_PASSWORD_LENGTH]
            );
        }
        if ($this->stringHelper->strlen(trim($password)) != $length) {
            throw new InputException('The password can not begin or end with a space.');
        }
    }

    /**
     * Create a hash for the given password
     *
     * @param string $password
     * @return string
     */
    protected function createPasswordHash($password)
    {
        return $this->encryptor->getHash($password, true);
    }

    /**
     * {@inheritdoc}
     */
    public function validate(\Magento\Customer\Api\Data\CustomerInterface $customer)
    {
        $customerErrors = $this->validator->validateData(
            $this->extensibleDataObjectConverter->toFlatArray($customer, [], '\Magento\Customer\Api\Data\CustomerInterface'),
            [],
            'customer'
        );

        if ($customerErrors !== true) {
            return $this->validationResultsDataBuilder
                ->setValid(false)
                ->setMessages($this->validator->getMessages())
                ->create();
        }

        $customerModel = $this->customerFactory->create()->updateData(
            $this->customerDataBuilder->populate($customer)->setAddresses([])->create()
        );

        $result = $customerModel->validate();
        if (true !== $result && is_array($result)) {
            return $this->validationResultsDataBuilder
                ->setValid(false)
                ->setMessages($result)
                ->create();
        }
        return $this->validationResultsDataBuilder
            ->setValid(true)
            ->setMessages([])
            ->create();
    }

    /**
     * {@inheritdoc}
     */
    public function isEmailAvailable($customerEmail, $websiteId = null)
    {
        try {
            if (is_null($websiteId)) {
                $websiteId = $this->storeManager->getStore()->getWebsiteId();
            }
            $this->customerRepository->get($customerEmail, $websiteId);
            return false;
        } catch (NoSuchEntityException $e) {
            return true;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function isCustomerInStore($customerWebsiteId, $storeId)
    {
        $ids = [];
        if ((bool)$this->configShare->isWebsiteScope()) {
            $ids = $this->storeManager->getWebsite($customerWebsiteId)->getStoreIds();
        } else {
            foreach ($this->storeManager->getStores() as $store) {
                $ids[] = $store->getId();
            }
        }

        return in_array($storeId, $ids);
    }

    /**
     * Validate the Reset Password Token for a customer.
     *
     * @param int $customerId
     * @param string $resetPasswordLinkToken
     * @return bool
     * @throws \Magento\Framework\Exception\State\InputMismatchException If token is mismatched
     * @throws \Magento\Framework\Exception\State\ExpiredException If token is expired
     * @throws \Magento\Framework\Exception\InputException If token or customer id is invalid
     * @throws \Magento\Framework\Exception\NoSuchEntityException If customer doesn't exist
     */
    private function validateResetPasswordToken($customerId, $resetPasswordLinkToken)
    {
        if (empty($customerId) || $customerId < 0) {
            $params = ['value' => $customerId, 'fieldName' => 'customerId'];
            throw new InputException(InputException::INVALID_FIELD_VALUE, $params);
        }
        if (!is_string($resetPasswordLinkToken) || empty($resetPasswordLinkToken)) {
            $params = ['fieldName' => 'resetPasswordLinkToken'];
            throw new InputException(InputException::REQUIRED_FIELD, $params);
        }

        $customerSecureData = $this->customerRegistry->retrieveSecureData($customerId);
        $rpToken = $customerSecureData->getRpToken();
        $rpTokenCreatedAt = $customerSecureData->getRpTokenCreatedAt();

        if (strcmp($rpToken, $resetPasswordLinkToken) !== 0) {
            throw new InputMismatchException('Reset password token mismatch.');
        } elseif ($this->isResetPasswordLinkTokenExpired($rpToken, $rpTokenCreatedAt)) {
            throw new ExpiredException('Reset password token expired.');
        }

        return true;
    }

    /**
     * Check if customer can be deleted.
     *
     * @param int $customerId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException If group is not found
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isReadonly($customerId)
    {
        $customer = $this->customerRegistry->retrieveSecureData($customerId);
        return !$customer->getDeleteable();
    }

    /**
     * Send email with new account related information
     *
     * @param CustomerInterface $customer
     * @param string $type
     * @param string $backUrl
     * @param string $storeId
     * @param string $sendemailStoreId
     * @return $this
     * @throws \Magento\Framework\Model\Exception
     */
    protected function sendNewAccountEmail(
        $customer,
        $type = 'registered',
        $backUrl = '',
        $storeId = '0',
        $sendemailStoreId = null
    ) {
        $types = $this->getTemplateTypes();

        if (!isset($types[$type])) {
            throw new \Magento\Framework\Model\Exception(__('Wrong transactional account email type'));
        }

        if (!$storeId) {
            $storeId = $this->getWebsiteStoreId($customer, $sendemailStoreId);
        }

        $store = $this->storeManager->getStore($customer->getStoreId());

        $customerEmailData = $this->getFullCustomerObject($customer);

        $this->sendEmailTemplate(
            $customer,
            $types[$type],
            self::XML_PATH_REGISTER_EMAIL_IDENTITY,
            ['customer' => $customerEmailData, 'back_url' => $backUrl, 'store' => $store],
            $storeId
        );

        return $this;
    }

    /**
     * Send email to customer when his password is reset
     *
     * @param CustomerInterface $customer
     * @return $this
     */
    protected function sendPasswordResetNotificationEmail($customer)
    {
        $storeId = $customer->getStoreId();
        if (!$storeId) {
            $storeId = $this->getWebsiteStoreId($customer);
        }

        /** @var \Magento\Framework\Mail\TransportInterface $transport */
        $transport = $this->transportBuilder->setTemplateIdentifier(
            $this->scopeConfig->getValue(
                self::XML_PATH_RESET_PASSWORD_TEMPLATE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            )
        )->setTemplateOptions(
            ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId]
        )->setTemplateVars(
            ['customer' => $customer, 'store' => $this->storeManager->getStore($storeId)]
        )->setFrom(
            $this->scopeConfig->getValue(
                self::XML_PATH_FORGOT_EMAIL_IDENTITY,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            )
        )->addTo(
            $customer->getEmail(),
            $this->customerViewHelper->getCustomerName($customer)
        )->getTransport();
        $transport->sendMessage();

        return $this;
    }

    /**
     * Get either first store ID from a set website or the provided as default
     *
     * @param CustomerInterface $customer
     * @param int|string|null $defaultStoreId
     * @return int
     */
    protected function getWebsiteStoreId($customer, $defaultStoreId = null)
    {
        if ($customer->getWebsiteId() != 0 && empty($defaultStoreId)) {
            $storeIds = $this->storeManager->getWebsite($customer->getWebsiteId())->getStoreIds();
            reset($storeIds);
            $defaultStoreId = current($storeIds);
        }
        return $defaultStoreId;
    }

    /**
     * @return array
     */
    protected function getTemplateTypes()
    {
        /**
         * 'registered'   welcome email, when confirmation is disabled
         * 'confirmed'    welcome email, when confirmation is enabled
         * 'confirmation' email with confirmation link
         */
        $types = [
            'registered' => self::XML_PATH_REGISTER_EMAIL_TEMPLATE,
            'confirmed' => self::XML_PATH_CONFIRMED_EMAIL_TEMPLATE,
            'confirmation' => self::XML_PATH_CONFIRM_EMAIL_TEMPLATE,
        ];
        return $types;
    }

    /**
     * Send corresponding email template
     *
     * @param CustomerInterface $customer
     * @param string $template configuration path of email template
     * @param string $sender configuration path of email identity
     * @param array $templateParams
     * @param int|null $storeId
     * @return $this
     */
    protected function sendEmailTemplate($customer, $template, $sender, $templateParams = [], $storeId = null)
    {
        /** @var \Magento\Framework\Mail\TransportInterface $transport */
        $transport = $this->transportBuilder->setTemplateIdentifier(
            $this->scopeConfig->getValue($template, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId)
        )->setTemplateOptions(
            ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId]
        )->setTemplateVars(
            $templateParams
        )->setFrom(
            $this->scopeConfig->getValue($sender, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId)
        )->addTo(
            $customer->getEmail(),
            $this->customerViewHelper->getCustomerName($customer)
        )->getTransport();
        $transport->sendMessage();

        return $this;
    }

    /**
     * Check if accounts confirmation is required in config
     *
     * @param CustomerInterface $customer
     * @return bool
     */
    protected function isConfirmationRequired($customer)
    {
        if ($this->canSkipConfirmation($customer)) {
            return false;
        }
        $storeId = $customer->getStoreId() ? $customer->getStoreId() : null;

        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_IS_CONFIRM,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check whether confirmation may be skipped when registering using certain email address
     *
     * @param CustomerInterface $customer
     * @return bool
     */
    protected function canSkipConfirmation($customer)
    {
        if (!$customer->getId()) {
            return false;
        }

        /* If an email was used to start the registration process and it is the same email as the one
           used to register, then this can skip confirmation.
           */
        $skipConfirmationIfEmail = $this->registry->registry("skip_confirmation_if_email");
        if (!$skipConfirmationIfEmail) {
            return false;
        }

        return strtolower($skipConfirmationIfEmail) === strtolower($customer->getEmail());
    }

    /**
     * Check if rpToken is expired
     *
     * @param string $rpToken
     * @param string $rpTokenCreatedAt
     * @return bool
     */
    public function isResetPasswordLinkTokenExpired($rpToken, $rpTokenCreatedAt)
    {
        if (empty($rpToken) || empty($rpTokenCreatedAt)) {
            return true;
        }

        $expirationPeriod = $this->customerModel->getResetPasswordLinkExpirationPeriod();

        $currentTimestamp = $this->dateTime->toTimestamp($this->dateTime->now());
        $tokenTimestamp = $this->dateTime->toTimestamp($rpTokenCreatedAt);
        if ($tokenTimestamp > $currentTimestamp) {
            return true;
        }

        $dayDifference = floor(($currentTimestamp - $tokenTimestamp) / (24 * 60 * 60));
        if ($dayDifference >= $expirationPeriod) {
            return true;
        }

        return false;
    }

    /**
     * Change reset password link token
     *
     * Stores new reset password link token
     *
     * @param CustomerInterface $customer
     * @param string $passwordLinkToken
     * @return bool
     * @throws InputException
     */
    public function changeResetPasswordLinkToken($customer, $passwordLinkToken)
    {
        if (!is_string($passwordLinkToken) || empty($passwordLinkToken)) {
            throw new InputException(
                InputException::INVALID_FIELD_VALUE,
                ['value' => $passwordLinkToken, 'fieldName' => 'password reset token']
            );
        }
        if (is_string($passwordLinkToken) && !empty($passwordLinkToken)) {
            $customerSecure = $this->customerRegistry->retrieveSecureData($customer->getId());
            $customerSecure->setRpToken($passwordLinkToken);
            $customerSecure->setRpTokenCreatedAt($this->dateTime->now());
            $this->customerRepository->save($customer);
        }
        return true;
    }

    /**
     * Send email with new customer password
     *
     * @param CustomerInterface $customer
     * @param string $newPasswordToken
     * @return $this
     */
    public function sendPasswordReminderEmail($customer, $newPasswordToken)
    {
        $this->url->setScope($customer->getStoreId());
        //TODO : Fix how template is built. Maybe Framework Object or create new Email template data model?
        // Check template to see what values need to be set in the data model to be passed
        // Need to set the reset_password_url property of the object
        $resetUrl = $this->url->getUrl(
            'customer/account/createPassword',
            [
                '_query' => ['id' => $customer->getId(), 'token' => $newPasswordToken],
                '_store' => $customer->getStoreId(),
                '_nosid' => true,
            ]
        );

        $customerEmailData = $this->getFullCustomerObject($customer);
        $customerEmailData->setResetPasswordUrl($resetUrl);

        $this->sendEmailTemplate(
            $customer,
            self::XML_PATH_REMIND_EMAIL_TEMPLATE,
            self::XML_PATH_FORGOT_EMAIL_IDENTITY,
            ['customer' => $customerEmailData, 'store' => $this->storeManager->getStore($customer->getStoreId())],
            $customer->getStoreId()
        );

        return $this;
    }

    /**
     * Send email with reset password confirmation link
     *
     * @param CustomerInterface $customer
     * @return $this
     */
    public function sendPasswordResetConfirmationEmail($customer)
    {
        $storeId = $customer->getStoreId();
        if (!$storeId) {
            $storeId = $this->getWebsiteStoreId($customer);
        }

        $customerEmailData = $this->getFullCustomerObject($customer);

        $this->sendEmailTemplate(
            $customer,
            self::XML_PATH_FORGOT_EMAIL_TEMPLATE,
            self::XML_PATH_FORGOT_EMAIL_IDENTITY,
            ['customer' => $customerEmailData, 'store' => $this->storeManager->getStore($storeId)],
            $storeId
        );

        return $this;
    }

    /**
     * Get address by id
     *
     * @param CustomerInterface $customer
     * @param int $addressId
     * @return AddressInterface|null
     */
    protected function getAddressById(CustomerInterface $customer, $addressId)
    {
        foreach ($customer->getAddresses() as $address) {
            if ($address->getId() == $addressId) {
                return $address;
            }
        }
        return null;
    }

    /**
     * Create an object with data merged from Customer and CustomerSecure
     *
     * @param CustomerInterface $customer
     * @return Data\CustomerSecure
     */
    protected function getFullCustomerObject($customer)
    {
        // No need to flatten the custom attributes or nested objects since the only usage is for email templates and
        // object passed for events
        $mergedCustomerData = $this->customerRegistry->retrieveSecureData($customer->getId());
        $customerData = $this->dataProcessor
            ->buildOutputDataArray($customer, '\Magento\Customer\Api\Data\CustomerInterface');
        $mergedCustomerData->addData($customerData);
        $mergedCustomerData->setData('name', $this->customerViewHelper->getCustomerName($customer));
        return $mergedCustomerData;
    }

    /**
     * Return hashed password, which can be directly saved to database.
     *
     * @param string $password
     * @return string
     */
    public function getPasswordHash($password)
    {
        return $this->encryptor->getHash($password);
    }
}
