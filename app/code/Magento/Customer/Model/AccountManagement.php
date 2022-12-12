<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model;

use JetBrains\PhpStorm\ArrayShape;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\ValidationResultsInterface;
use Magento\Customer\Api\Data\ValidationResultsInterfaceFactory;
use Magento\Customer\Api\SessionCleanerInterface;
use Magento\Customer\Helper\View as CustomerViewHelper;
use Magento\Customer\Model\Config\Share as ConfigShare;
use Magento\Customer\Model\Customer as CustomerModel;
use Magento\Customer\Model\Customer\CredentialsValidator;
use Magento\Customer\Model\ForgotPasswordToken\GetCustomerByToken;
use Magento\Customer\Model\Logger as CustomerLogger;
use Magento\Customer\Model\Metadata\Validator;
use Magento\Customer\Model\ResourceModel\Visitor\CollectionFactory;
use Magento\Directory\Model\AllowedCountries;
use Magento\Eav\Model\Validator\Attribute\Backend;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\DataObjectFactory as ObjectFactory;
use Magento\Framework\Encryption\EncryptorInterface as Encryptor;
use Magento\Framework\Encryption\Helper\Security;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\ExpiredException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Framework\Exception\State\UserLockedException;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Math\Random;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Registry;
use Magento\Framework\Session\SaveHandlerInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\StringUtils as StringHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface as PsrLogger;

/**
 * Handle various customer account actions
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class AccountManagement implements AccountManagementInterface
{
    private const GUEST_CHECKOUT_LOGIN_OPTION_SYS_CONFIG = 'checkout/options/disable_guest_checkout_login';

    /**
     * Configuration paths for create account email template
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see EmailNotification::XML_PATH_REGISTER_EMAIL_TEMPLATE
     */
    public const XML_PATH_REGISTER_EMAIL_TEMPLATE = 'customer/create_account/email_template';

    /**
     * Configuration paths for register no password email template
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see EmailNotification::XML_PATH_REGISTER_EMAIL_TEMPLATE
     */
    public const XML_PATH_REGISTER_NO_PASSWORD_EMAIL_TEMPLATE = 'customer/create_account/email_no_password_template';

    /**
     * Configuration paths for remind email identity
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see EmailNotification::XML_PATH_REGISTER_EMAIL_TEMPLATE
     */
    public const XML_PATH_REGISTER_EMAIL_IDENTITY = 'customer/create_account/email_identity';

    /**
     * Configuration paths for remind email template
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see EmailNotification::XML_PATH_REGISTER_EMAIL_TEMPLATE
     */
    public const XML_PATH_REMIND_EMAIL_TEMPLATE = 'customer/password/remind_email_template';

    /**
     * Configuration paths for forgot email email template
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see EmailNotification::XML_PATH_REGISTER_EMAIL_TEMPLATE
     */
    public const XML_PATH_FORGOT_EMAIL_TEMPLATE = 'customer/password/forgot_email_template';

    /**
     * Configuration paths for forgot email identity
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see EmailNotification::XML_PATH_REGISTER_EMAIL_TEMPLATE
     */
    public const XML_PATH_FORGOT_EMAIL_IDENTITY = 'customer/password/forgot_email_identity';

    /**
     * Configuration paths for account confirmation required
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see AccountConfirmation::XML_PATH_IS_CONFIRM
     */
    public const XML_PATH_IS_CONFIRM = 'customer/create_account/confirm';

    /**
     * Configuration paths for account confirmation email template
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see EmailNotification::XML_PATH_REGISTER_EMAIL_TEMPLATE
     */
    public const XML_PATH_CONFIRM_EMAIL_TEMPLATE = 'customer/create_account/email_confirmation_template';

    /**
     * Configuration paths for confirmation confirmed email template
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see EmailNotification::XML_PATH_REGISTER_EMAIL_TEMPLATE
     */
    public const XML_PATH_CONFIRMED_EMAIL_TEMPLATE = 'customer/create_account/email_confirmed_template';

    /**
     * Constants for the type of new account email to be sent
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see EmailNotificationInterface::NEW_ACCOUNT_EMAIL_REGISTERED
     */
    public const NEW_ACCOUNT_EMAIL_REGISTERED = 'registered';

    /**
     * Welcome email, when password setting is required
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see EmailNotificationInterface::NEW_ACCOUNT_EMAIL_REGISTERED
     */
    public const NEW_ACCOUNT_EMAIL_REGISTERED_NO_PASSWORD = 'registered_no_password';

    /**
     * Welcome email, when confirmation is enabled
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see EmailNotificationInterface::NEW_ACCOUNT_EMAIL_REGISTERED
     */
    public const NEW_ACCOUNT_EMAIL_CONFIRMATION = 'confirmation';

    /**
     * Confirmation email, when account is confirmed
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see EmailNotificationInterface::NEW_ACCOUNT_EMAIL_REGISTERED
     */
    public const NEW_ACCOUNT_EMAIL_CONFIRMED = 'confirmed';

    /**
     * Constants for types of emails to send out.
     * pdl:
     * forgot, remind, reset email templates
     */
    public const EMAIL_REMINDER = 'email_reminder';

    public const EMAIL_RESET = 'email_reset';

    /**
     * Configuration path to customer password minimum length
     */
    public const XML_PATH_MINIMUM_PASSWORD_LENGTH = 'customer/password/minimum_password_length';

    /**
     * Configuration path to customer password required character classes number
     */
    public const XML_PATH_REQUIRED_CHARACTER_CLASSES_NUMBER = 'customer/password/required_character_classes_number';

    /**
     * Configuration path to customer reset password email template
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see Magento/Customer/Model/EmailNotification::XML_PATH_REGISTER_EMAIL_TEMPLATE
     */
    public const XML_PATH_RESET_PASSWORD_TEMPLATE = 'customer/password/reset_password_template';

    /**
     * Minimum password length
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see \Magento\Customer\Model\AccountManagement::XML_PATH_MINIMUM_PASSWORD_LENGTH
     */
    public const MIN_PASSWORD_LENGTH = 6;

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Customer::manage';

    /**
     * @var CustomerFactory
     */
    private CustomerFactory $customerFactory;

    /**
     * @var ValidationResultsInterfaceFactory
     */
    private ValidationResultsInterfaceFactory $validationResultsDataFactory;

    /**
     * @var ManagerInterface
     */
    private ManagerInterface $eventManager;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var Random
     */
    private Random $mathRandom;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var AddressRepositoryInterface
     */
    private AddressRepositoryInterface $addressRepository;

    /**
     * @var CustomerMetadataInterface
     */
    private CustomerMetadataInterface $customerMetadataService;

    /**
     * @var PsrLogger
     */
    protected PsrLogger $logger;

    /**
     * @var Encryptor
     */
    private Encryptor $encryptor;

    /**
     * @var CustomerRegistry
     */
    private CustomerRegistry $customerRegistry;

    /**
     * @var ConfigShare
     */
    private ConfigShare $configShare;

    /**
     * @var StringHelper
     */
    protected StringHelper $stringHelper;

    /**
     * @var CustomerRepositoryInterface
     */
    private CustomerRepositoryInterface $customerRepository;

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @var TransportBuilder
     */
    private TransportBuilder $transportBuilder;

    /**
     * @var DataObjectProcessor
     */
    protected DataObjectProcessor $dataProcessor;

    /**
     * @var Registry
     */
    protected Registry $registry;

    /**
     * @var CustomerViewHelper
     */
    protected CustomerViewHelper $customerViewHelper;

    /**
     * @var DateTime
     */
    protected DateTime $dateTime;

    /**
     * @var ObjectFactory
     */
    protected ObjectFactory $objectFactory;

    /**
     * @var ExtensibleDataObjectConverter
     */
    protected ExtensibleDataObjectConverter $extensibleDataObjectConverter;

    /**
     * @var CustomerModel
     */
    protected Customer $customerModel;

    /**
     * @var AuthenticationInterface
     */
    protected AuthenticationInterface $authentication;

    /**
     * @var EmailNotificationInterface
     */
    private EmailNotificationInterface $emailNotification;

    /**
     * @var Backend
     */
    private Backend $eavValidator;

    /**
     * @var CredentialsValidator
     */
    private CredentialsValidator $credentialsValidator;

    /**
     * @var DateTimeFactory
     */
    private DateTimeFactory $dateTimeFactory;

    /**
     * @var AccountConfirmation
     */
    private AccountConfirmation $accountConfirmation;

    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var AddressRegistry
     */
    private AddressRegistry $addressRegistry;

    /**
     * @var AllowedCountries
     */
    private AllowedCountries $allowedCountriesReader;

    /**
     * @var GetCustomerByToken
     */
    private GetCustomerByToken $getByToken;

    /**
     * @var SessionCleanerInterface
     */
    private SessionCleanerInterface $sessionCleaner;

    /**
     * @var AuthorizationInterface
     */
    private AuthorizationInterface $authorization;

    /**
     * @var CustomerLogger
     */
    private CustomerLogger $customerLogger;

    /**
     * @param CustomerFactory $customerFactory
     * @param ManagerInterface $eventManager
     * @param StoreManagerInterface $storeManager
     * @param Random $mathRandom
     * @param Validator $validator
     * @param ValidationResultsInterfaceFactory $validationResultsDataFactory
     * @param AddressRepositoryInterface $addressRepository
     * @param CustomerMetadataInterface $customerMetadataService
     * @param CustomerRegistry $customerRegistry
     * @param PsrLogger $logger
     * @param Encryptor $encryptor
     * @param ConfigShare $configShare
     * @param StringHelper $stringHelper
     * @param CustomerRepositoryInterface $customerRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param TransportBuilder $transportBuilder
     * @param DataObjectProcessor $dataProcessor
     * @param Registry $registry
     * @param CustomerViewHelper $customerViewHelper
     * @param DateTime $dateTime
     * @param CustomerModel $customerModel
     * @param ObjectFactory $objectFactory
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param CredentialsValidator|null $credentialsValidator
     * @param DateTimeFactory|null $dateTimeFactory
     * @param AccountConfirmation|null $accountConfirmation
     * @param SessionManagerInterface|null $sessionManager
     * @param SaveHandlerInterface|null $saveHandler
     * @param CollectionFactory|null $visitorCollectionFactory
     * @param SearchCriteriaBuilder|null $searchCriteriaBuilder
     * @param AddressRegistry|null $addressRegistry
     * @param GetCustomerByToken|null $getByToken
     * @param AllowedCountries|null $allowedCountriesReader
     * @param SessionCleanerInterface|null $sessionCleaner
     * @param AuthorizationInterface|null $authorization
     * @param AuthenticationInterface|null $authentication
     * @param Backend|null $eavValidator
     * @param CustomerLogger|null $customerLogger
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.LongVariable)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        CustomerFactory $customerFactory,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        Random $mathRandom,
        Validator $validator,
        ValidationResultsInterfaceFactory $validationResultsDataFactory,
        AddressRepositoryInterface $addressRepository,
        CustomerMetadataInterface $customerMetadataService,
        CustomerRegistry $customerRegistry,
        PsrLogger $logger,
        Encryptor $encryptor,
        ConfigShare $configShare,
        StringHelper $stringHelper,
        CustomerRepositoryInterface $customerRepository,
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder,
        DataObjectProcessor $dataProcessor,
        Registry $registry,
        CustomerViewHelper $customerViewHelper,
        DateTime $dateTime,
        CustomerModel $customerModel,
        ObjectFactory $objectFactory,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        CredentialsValidator $credentialsValidator = null,
        DateTimeFactory $dateTimeFactory = null,
        AccountConfirmation $accountConfirmation = null,
        SessionManagerInterface $sessionManager = null,
        SaveHandlerInterface $saveHandler = null,
        CollectionFactory $visitorCollectionFactory = null,
        SearchCriteriaBuilder $searchCriteriaBuilder = null,
        AddressRegistry $addressRegistry = null,
        GetCustomerByToken $getByToken = null,
        AllowedCountries $allowedCountriesReader = null,
        SessionCleanerInterface $sessionCleaner = null,
        AuthorizationInterface $authorization = null,
        AuthenticationInterface $authentication = null,
        Backend $eavValidator = null,
        ?CustomerLogger $customerLogger = null
    ) {
        $this->customerFactory = $customerFactory;
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
        $this->mathRandom = $mathRandom;
        $this->validator = $validator;
        $this->validationResultsDataFactory = $validationResultsDataFactory;
        $this->addressRepository = $addressRepository;
        $this->customerMetadataService = $customerMetadataService;
        $this->customerRegistry = $customerRegistry;
        $this->logger = $logger;
        $this->encryptor = $encryptor;
        $this->configShare = $configShare;
        $this->stringHelper = $stringHelper;
        $this->customerRepository = $customerRepository;
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->dataProcessor = $dataProcessor;
        $this->registry = $registry;
        $this->customerViewHelper = $customerViewHelper;
        $this->dateTime = $dateTime;
        $this->customerModel = $customerModel;
        $this->objectFactory = $objectFactory;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
        $objectManager = ObjectManager::getInstance();
        $this->credentialsValidator =
            $credentialsValidator ?: $objectManager->get(CredentialsValidator::class);
        $this->dateTimeFactory = $dateTimeFactory ?: $objectManager->get(DateTimeFactory::class);
        $this->accountConfirmation = $accountConfirmation ?: $objectManager
            ->get(AccountConfirmation::class);
        $this->searchCriteriaBuilder = $searchCriteriaBuilder
            ?: $objectManager->get(SearchCriteriaBuilder::class);
        $this->addressRegistry = $addressRegistry
            ?: $objectManager->get(AddressRegistry::class);
        $this->getByToken = $getByToken
            ?: $objectManager->get(GetCustomerByToken::class);
        $this->allowedCountriesReader = $allowedCountriesReader
            ?: $objectManager->get(AllowedCountries::class);
        $this->sessionCleaner = $sessionCleaner ?? $objectManager->get(SessionCleanerInterface::class);
        $this->authorization = $authorization ?? $objectManager->get(AuthorizationInterface::class);
        $this->authentication = $authentication ?? $objectManager->get(AuthenticationInterface::class);
        $this->eavValidator = $eavValidator ?? $objectManager->get(Backend::class);
        $this->customerLogger = $customerLogger ?? $objectManager->get(CustomerLogger::class);
    }

    /**
     * @inheritdoc
     *
     * @param string $email
     * @param int $websiteId
     * @param string $redirectUrl
     * @return bool
     * @throws InvalidTransitionException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function resendConfirmation(string $email, int $websiteId, string $redirectUrl = ''): bool
    {
        $customer = $this->customerRepository->get($email, $websiteId);
        if (!$customer->getConfirmation()) {
            throw new InvalidTransitionException(__("Confirmation isn't needed."));
        }

        try {
            $this->getEmailNotification()->newAccount(
                $customer,
                self::NEW_ACCOUNT_EMAIL_CONFIRMATION,
                $redirectUrl,
                $this->storeManager->getStore()->getId()
            );
        } catch (MailException $e) {
            // If we are not able to send a new account email, this should be ignored
            $this->logger->critical($e);

            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     *
     * @param string $email
     * @param string $confirmationKey
     * @return CustomerInterface
     * @throws InputException
     * @throws InputMismatchException
     * @throws InvalidTransitionException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function activate(string $email, string $confirmationKey): CustomerInterface
    {
        $customer = $this->customerRepository->get($email);
        return $this->activateCustomer($customer, $confirmationKey);
    }

    /**
     * @inheritdoc
     *
     * @param int $customerId
     * @param string $confirmationKey
     * @return CustomerInterface
     * @throws InputException
     * @throws InputMismatchException
     * @throws InvalidTransitionException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function activateById(int $customerId, string $confirmationKey): CustomerInterface
    {
        $customer = $this->customerRepository->getById($customerId);
        return $this->activateCustomer($customer, $confirmationKey);
    }

    /**
     * Activate a customer account using a key that was sent in a confirmation email.
     *
     * @param CustomerInterface $customer
     * @param string $confirmationKey
     * @return CustomerInterface
     * @throws InputException
     * @throws InputMismatchException
     * @throws InvalidTransitionException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function activateCustomer(CustomerInterface $customer, string $confirmationKey): CustomerInterface
    {
        // check if customer is inactive
        if (!$customer->getConfirmation()) {
            throw new InvalidTransitionException(__('The account is already active.'));
        }

        if ($customer->getConfirmation() !== $confirmationKey) {
            throw new InputMismatchException(__('The confirmation token is invalid. Verify the token and try again.'));
        }

        $customer->setConfirmation(null);
        // No need to validate customer and customer address while activating customer
        $this->setIgnoreValidationFlag($customer);
        $this->customerRepository->save($customer);

        $customerLastLoginAt = $this->customerLogger->get((int)$customer->getId())->getLastLoginAt();
        if (!$customerLastLoginAt) {
            $this->getEmailNotification()->newAccount(
                $customer,
                'confirmed',
                '',
                $this->storeManager->getStore()->getId()
            );
        }

        return $customer;
    }

    /**
     * @inheritdoc
     *
     * @param string $email
     * @param string $password
     * @return CustomerInterface
     * @throws EmailNotConfirmedException
     * @throws InvalidEmailOrPasswordException
     * @throws LocalizedException
     * @throws UserLockedException
     */
    public function authenticate(string $email, string $password): CustomerInterface
    {
        try {
            $customer = $this->customerRepository->get($email);
        } catch (NoSuchEntityException $e) {
            throw new InvalidEmailOrPasswordException(__('Invalid login or password.'));
        }

        $customerId = $customer->getId();
        if ($this->authentication->isLocked($customerId)) {
            throw new UserLockedException(__('The account is locked.'));
        }
        try {
            $this->authentication->authenticate($customerId, $password);
        } catch (InvalidEmailOrPasswordException $e) {
            throw new InvalidEmailOrPasswordException(__('Invalid login or password.'));
        }

        if ($customer->getConfirmation()
            && ($this->isConfirmationRequired($customer) || $this->isEmailChangedConfirmationRequired($customer))) {
            throw new EmailNotConfirmedException(__("This account isn't confirmed. Verify and try again."));
        }

        $customerModel = $this->customerFactory->create()->updateData($customer);
        $this->eventManager->dispatch(
            'customer_customer_authenticated',
            ['model' => $customerModel, 'password' => $password]
        );

        $this->eventManager->dispatch('customer_data_object_login', ['customer' => $customer]);

        return $customer;
    }

    /**
     * Checks if account confirmation is required if the email address has been changed
     *
     * @param CustomerInterface $customer
     * @return bool
     */
    private function isEmailChangedConfirmationRequired(CustomerInterface $customer): bool
    {
        return $this->accountConfirmation->isEmailChangedConfirmationRequired(
            (int)$customer->getWebsiteId(),
            (int)$customer->getId(),
            $customer->getEmail()
        );
    }

    /**
     * @inheritdoc
     *
     * @param int $customerId
     * @param string $resetPasswordLinkToken
     * @return bool
     * @throws ExpiredException
     * @throws InputException
     * @throws InputMismatchException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function validateResetPasswordLinkToken(int $customerId, string $resetPasswordLinkToken): bool
    {
        $this->validateResetPasswordToken($customerId, $resetPasswordLinkToken);
        return true;
    }

    /**
     * @inheritdoc
     *
     * @param string $email
     * @param string $template
     * @param int|null $websiteId
     * @return bool
     * @throws InputException
     * @throws InputMismatchException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function initiatePasswordReset(string $email, string $template, int $websiteId = null): bool
    {
        if ($websiteId === null) {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
        }
        // load customer by email
        $customer = $this->customerRepository->get($email, $websiteId);

        // No need to validate customer address while saving customer reset password token
        $this->disableAddressValidation($customer);

        $newPasswordToken = $this->mathRandom->getUniqueHash();
        $this->changeResetPasswordLinkToken($customer, $newPasswordToken);

        try {
            switch ($template) {
                case AccountManagement::EMAIL_REMINDER:
                    $this->getEmailNotification()->passwordReminder($customer);
                    break;
                case AccountManagement::EMAIL_RESET:
                    $this->getEmailNotification()->passwordResetConfirmation($customer);
                    break;
                default:
                    $this->handleUnknownTemplate($template);
                    break;
            }
            return true;
        } catch (MailException $e) {
            // If we are not able to send a reset password email, this should be ignored
            $this->logger->critical($e);
        }
        return false;
    }

    /**
     * Handle not supported template
     *
     * @param string $template
     * @throws InputException
     */
    private function handleUnknownTemplate(string $template)
    {
        throw new InputException(
            __(
                'Invalid value of "%value" provided for the %fieldName field. '
                    . 'Possible values: %template1 or %template2.',
                [
                    'value' => $template,
                    'fieldName' => 'template',
                    'template1' => AccountManagement::EMAIL_REMINDER,
                    'template2' => AccountManagement::EMAIL_RESET
                ]
            )
        );
    }

    /**
     * @inheritdoc
     *
     * @param string $email
     * @param string $resetToken
     * @param string $newPassword
     * @return bool
     * @throws ExpiredException
     * @throws InputException
     * @throws InputMismatchException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function resetPassword(string $email, string $resetToken, string $newPassword): bool
    {
        if (!$email) {
            $params = ['fieldName' => 'email'];
            throw new InputException(__('"%fieldName" is required. Enter and try again.', $params));
        } else {
            $customer = $this->customerRepository->get($email);
        }

        // No need to validate customer and customer address while saving customer reset password token
        $this->disableAddressValidation($customer);
        $this->setIgnoreValidationFlag($customer);

        //Validate Token and new password strength
        $this->validateResetPasswordToken((int)$customer->getId(), $resetToken);
        $this->credentialsValidator->checkPasswordDifferentFromEmail(
            $email,
            $newPassword
        );
        $this->checkPasswordStrength($newPassword);
        //Update secure data
        $customerSecure = $this->customerRegistry->retrieveSecureData($customer->getId());
        $customerSecure->setRpToken(null);
        $customerSecure->setRpTokenCreatedAt(null);
        $customerSecure->setPasswordHash($this->createPasswordHash($newPassword));
        $customerSecure->setFailuresNum(0);
        $customerSecure->setFirstFailure(null);
        $customerSecure->setLockExpires(null);
        $this->sessionCleaner->clearFor((int)$customer->getId());
        $this->customerRepository->save($customer);

        return true;
    }

    /**
     * Make sure that password complies with minimum security requirements.
     *
     * @param string $password
     * @return void
     * @throws InputException
     */
    protected function checkPasswordStrength(string $password)
    {
        $length = $this->stringHelper->strlen($password);
        if ($length > self::MAX_PASSWORD_LENGTH) {
            throw new InputException(
                __(
                    'Please enter a password with at most %1 characters.',
                    self::MAX_PASSWORD_LENGTH
                )
            );
        }
        $configMinPasswordLength = $this->getMinPasswordLength();
        if ($length < $configMinPasswordLength) {
            throw new InputException(
                __(
                    'The password needs at least %1 characters. Create a new password and try again.',
                    $configMinPasswordLength
                )
            );
        }
        $trimmedPassLength = $this->stringHelper->strlen($password === null ? '' : trim($password));
        if ($trimmedPassLength != $length) {
            throw new InputException(
                __("The password can't begin or end with a space. Verify the password and try again.")
            );
        }

        $requiredCharactersCheck = $this->makeRequiredCharactersCheck($password);
        if ($requiredCharactersCheck !== 0) {
            throw new InputException(
                __(
                    'Minimum of different classes of characters in password is %1.' .
                    ' Classes of characters: Lower Case, Upper Case, Digits, Special Characters.',
                    $requiredCharactersCheck
                )
            );
        }
    }

    /**
     * Check password for presence of required character sets
     *
     * @param string $password
     * @return int
     */
    protected function makeRequiredCharactersCheck(string $password): int
    {
        $counter = 0;
        $requiredNumber = $this->scopeConfig->getValue(self::XML_PATH_REQUIRED_CHARACTER_CLASSES_NUMBER);
        $return = 0;

        if ($password !== null) {
            if (preg_match('/[0-9]+/', $password)) {
                $counter++;
            }
            if (preg_match('/[A-Z]+/', $password)) {
                $counter++;
            }
            if (preg_match('/[a-z]+/', $password)) {
                $counter++;
            }
            if (preg_match('/[^a-zA-Z0-9]+/', $password)) {
                $counter++;
            }
        }

        if ($counter < $requiredNumber) {
            $return = $requiredNumber;
        }

        return $return;
    }

    /**
     * Retrieve minimum password length
     *
     * @return int
     */
    protected function getMinPasswordLength(): int
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MINIMUM_PASSWORD_LENGTH);
    }

    /**
     * @inheritdoc
     *
     * @param int $customerId
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getConfirmationStatus(int $customerId): string
    {
        // load customer by id
        $customer = $this->customerRepository->getById($customerId);

        return $this->isConfirmationRequired($customer)
            ? $customer->getConfirmation() ? self::ACCOUNT_CONFIRMATION_REQUIRED : self::ACCOUNT_CONFIRMED
            : self::ACCOUNT_CONFIRMATION_NOT_REQUIRED;
    }

    /**
     * @inheritdoc
     *
     * @throws LocalizedException
     */
    public function createAccount(CustomerInterface $customer, $password = null, $redirectUrl = '')
    {
        $groupId = $customer->getGroupId();
        if (isset($groupId) && !$this->authorization->isAllowed(self::ADMIN_RESOURCE)) {
            $customer->setGroupId(null);
        }

        if ($password !== null) {
            $this->checkPasswordStrength($password);
            $customerEmail = $customer->getEmail();
            try {
                $this->credentialsValidator->checkPasswordDifferentFromEmail($customerEmail, $password);
            } catch (InputException $e) {
                throw new LocalizedException(
                    __("The password can't be the same as the email address. Create a new password and try again.")
                );
            }
            $hash = $this->createPasswordHash($password);
        } else {
            $hash = null;
        }
        return $this->createAccountWithPasswordHash($customer, $hash, $redirectUrl);
    }

    /**
     * @inheritdoc
     *
     * @param CustomerInterface $customer
     * @param string $hash
     * @param string $redirectUrl
     * @return CustomerInterface
     * @throws InputException
     * @throws InputMismatchException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function createAccountWithPasswordHash(CustomerInterface $customer, string $hash, string $redirectUrl = ''): CustomerInterface
    {
        // This logic allows an existing customer to be added to a different store.  No new account is created.
        // The plan is to move this logic into a new method called something like 'registerAccountWithStore'
        if ($customer->getId()) {
            $customer = $this->customerRepository->get($customer->getEmail());
            $websiteId = $customer->getWebsiteId();

            if ($this->isCustomerInStore($websiteId, $customer->getStoreId())) {
                throw new InputException(__('This customer already exists in this store.'));
            }
            // Existing password hash will be used from secured customer data registry when saving customer
        }

        // Make sure we have a storeId to associate this customer with.
        if (!$customer->getStoreId()) {
            if ($customer->getWebsiteId()) {
                $storeId = $this->storeManager->getWebsite($customer->getWebsiteId())->getDefaultStore()->getId();
            } else {
                $this->storeManager->setCurrentStore(null);
                $storeId = $this->storeManager->getStore()->getId();
            }
            $customer->setStoreId($storeId);
        }

        // Associate website_id with customer
        if (!$customer->getWebsiteId()) {
            $websiteId = $this->storeManager->getStore($customer->getStoreId())->getWebsiteId();
            $customer->setWebsiteId($websiteId);
        }

        $this->validateCustomerStoreIdByWebsiteId($customer);

        // Update 'created_in' value with actual store name
        if ($customer->getId() === null) {
            $storeName = $this->storeManager->getStore($customer->getStoreId())->getName();
            $customer->setCreatedIn($storeName);
        }

        $customerAddresses = $customer->getAddresses() ?: [];
        $customer->setAddresses(null);
        try {
            // If customer exists existing hash will be used by Repository
            $customer = $this->customerRepository->save($customer, $hash);
        } catch (AlreadyExistsException $e) {
            throw new InputMismatchException(
                __('A customer with the same email address already exists in an associated website.')
            );
        } catch (LocalizedException $e) {
            throw $e;
        }
        try {
            foreach ($customerAddresses as $address) {
                if (!$this->isAddressAllowedForWebsite($address, $customer->getStoreId())) {
                    continue;
                }
                if ($address->getId()) {
                    $newAddress = clone $address;
                    $newAddress->setId(null);
                    $newAddress->setCustomerId($customer->getId());
                    $this->addressRepository->save($newAddress);
                } else {
                    $address->setCustomerId($customer->getId());
                    $this->addressRepository->save($address);
                }
            }
            $this->customerRegistry->remove($customer->getId());
        } catch (InputException $e) {
            $this->customerRepository->delete($customer);
            throw $e;
        }
        $customer = $this->customerRepository->getById($customer->getId());
        $newLinkToken = $this->mathRandom->getUniqueHash();
        $this->changeResetPasswordLinkToken($customer, $newLinkToken);
        $this->sendEmailConfirmation($customer, $redirectUrl);

        return $customer;
    }

    /**
     * @inheritdoc
     *
     * @param int $customerId
     * @return AddressInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getDefaultBillingAddress(int $customerId): AddressInterface
    {
        $customer = $this->customerRepository->getById($customerId);
        return $this->getAddressById($customer, (int) $customer->getDefaultBilling());
    }

    /**
     * @inheritdoc
     *
     * @param int $customerId
     * @return AddressInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getDefaultShippingAddress(int $customerId): AddressInterface
    {
        $customer = $this->customerRepository->getById($customerId);
        return $this->getAddressById($customer, (int) $customer->getDefaultShipping());
    }

    /**
     * Send either confirmation or welcome email after an account creation
     *
     * @param CustomerInterface $customer
     * @param string $redirectUrl
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function sendEmailConfirmation(CustomerInterface $customer, string $redirectUrl)
    {
        try {
            $hash = $this->customerRegistry->retrieveSecureData($customer->getId())->getPasswordHash();
            $templateType = self::NEW_ACCOUNT_EMAIL_REGISTERED;
            if ($this->isConfirmationRequired($customer) && $hash != '') {
                $templateType = self::NEW_ACCOUNT_EMAIL_CONFIRMATION;
            } elseif ($hash == '') {
                $templateType = self::NEW_ACCOUNT_EMAIL_REGISTERED_NO_PASSWORD;
            }
            $this->getEmailNotification()->newAccount($customer, $templateType, $redirectUrl, $customer->getStoreId());
        } catch (MailException $e) {
            // If we are not able to send a new account email, this should be ignored
            $this->logger->critical($e);
        } catch (\UnexpectedValueException $e) {
            $this->logger->error($e);
        }
    }

    /**
     * @param string $email
     * @param string $currentPassword
     * @param string $newPassword
     * @return bool
     * @throws InputException
     * @throws InputMismatchException
     * @throws InvalidEmailOrPasswordException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws UserLockedException
     */
    public function changePassword(string $email, string $currentPassword, string $newPassword): bool
    {
        try {
            $customer = $this->customerRepository->get($email);
        } catch (NoSuchEntityException $e) {
            throw new InvalidEmailOrPasswordException(__('Invalid login or password.'));
        }
        return $this->changePasswordForCustomer($customer, $currentPassword, $newPassword);
    }

    /**
     * @param int $customerId
     * @param string $currentPassword
     * @param string $newPassword
     * @return bool
     * @throws InputException
     * @throws InputMismatchException
     * @throws InvalidEmailOrPasswordException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws UserLockedException
     */
    public function changePasswordById(int $customerId, string $currentPassword, string $newPassword): bool
    {
        try {
            $customer = $this->customerRepository->getById($customerId);
        } catch (NoSuchEntityException $e) {
            throw new InvalidEmailOrPasswordException(__('Invalid login or password.'));
        }
        return $this->changePasswordForCustomer($customer, $currentPassword, $newPassword);
    }

    /**
     * Change customer password
     *
     * @param CustomerInterface $customer
     * @param string $currentPassword
     * @param string $newPassword
     * @return bool true on success
     * @throws InputException
     * @throws InputMismatchException
     * @throws InvalidEmailOrPasswordException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws UserLockedException
     */
    private function changePasswordForCustomer(CustomerInterface $customer, string $currentPassword, string $newPassword): bool
    {
        try {
            $this->authentication->authenticate($customer->getId(), $currentPassword);
        } catch (InvalidEmailOrPasswordException $e) {
            throw new InvalidEmailOrPasswordException(
                __("The password doesn't match this account. Verify the password and try again.")
            );
        }
        $customerEmail = $customer->getEmail();
        $this->credentialsValidator->checkPasswordDifferentFromEmail($customerEmail, $newPassword);
        $this->checkPasswordStrength($newPassword);
        $customerSecure = $this->customerRegistry->retrieveSecureData($customer->getId());
        $customerSecure->setRpToken(null);
        $customerSecure->setRpTokenCreatedAt(null);
        $customerSecure->setPasswordHash($this->createPasswordHash($newPassword));
        $this->sessionCleaner->clearFor((int)$customer->getId());
        $this->disableAddressValidation($customer);
        $this->customerRepository->save($customer);

        return true;
    }

    /**
     * Create a hash for the given password
     *
     * @param string $password
     * @return string
     */
    protected function createPasswordHash(string $password): string
    {
        return $this->encryptor->getHash($password, true);
    }

    /**
     * @param CustomerInterface $customer
     * @return ValidationResultsInterface|string[]
     */
    public function validate(CustomerInterface $customer): ValidationResultsInterface
    {
        $validationResults = $this->validationResultsDataFactory->create();

        $oldAddresses = $customer->getAddresses();
        $customerModel = $this->customerFactory->create()->updateData(
            $customer->setAddresses([])
        );
        $customer->setAddresses($oldAddresses);

        $result = $this->eavValidator->isValid($customerModel);
        if ($result === false && is_array($this->eavValidator->getMessages())) {
            return $validationResults->setIsValid(false)->setMessages(
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                call_user_func_array(
                    'array_merge',
                    array_values($this->eavValidator->getMessages())
                )
            );
        }
        return $validationResults->setIsValid(true)->setMessages([]);
    }

    /**
     * @inheritdoc
     */
    public function isEmailAvailable(string $customerEmail, int $websiteId = null): bool
    {
        $guestLoginConfig = $this->scopeConfig->getValue(
            self::GUEST_CHECKOUT_LOGIN_OPTION_SYS_CONFIG,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );

        if ($guestLoginConfig) {
            return true;
        }

        try {
            if ($websiteId === null) {
                $websiteId = $this->storeManager->getStore()->getWebsiteId();
            }
            $this->customerRepository->get($customerEmail, $websiteId);
            return false;
        } catch (NoSuchEntityException $e) {
            return true;
        }
    }

    /**
     * @param int $customerWebsiteId
     * @param int $storeId
     * @return bool
     * @throws LocalizedException
     */
    public function isCustomerInStore(int $customerWebsiteId, int $storeId): bool
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
     * Validate customer store id by customer website id.
     *
     * @param CustomerInterface $customer
     * @return bool
     * @throws LocalizedException
     */
    public function validateCustomerStoreIdByWebsiteId(CustomerInterface $customer): bool
    {
        if (!$this->isCustomerInStore($customer->getWebsiteId(), $customer->getStoreId())) {
            throw new LocalizedException(__('The store view is not in the associated website.'));
        }

        return true;
    }

    /**
     * Validate the Reset Password Token for a customer.
     *
     * @param int $customerId
     * @param string $resetPasswordLinkToken
     * @return void
     * @throws ExpiredException If token is expired
     * @throws InputException If token or customer id is invalid
     * @throws InputMismatchException If token is mismatched
     * @throws LocalizedException
     * @throws NoSuchEntityException If customer doesn't exist
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    private function validateResetPasswordToken(int $customerId, string $resetPasswordLinkToken): void
    {
        if (!$customerId) {
            throw new InputException(
                __(
                    'Invalid value of "%value" provided for the %fieldName field.',
                    ['value' => $customerId, 'fieldName' => 'customerId']
                )
            );
        }
        if (!$resetPasswordLinkToken) {
            $params = ['fieldName' => 'resetPasswordLinkToken'];
            throw new InputException(__('"%fieldName" is required. Enter and try again.', $params));
        }
        $customerSecureData = $this->customerRegistry->retrieveSecureData($customerId);
        $rpToken = $customerSecureData->getRpToken();
        $rpTokenCreatedAt = $customerSecureData->getRpTokenCreatedAt();
        if (!Security::compareStrings($rpToken, $resetPasswordLinkToken)) {
            throw new InputMismatchException(__('The password token is mismatched. Reset and try again.'));
        } elseif ($this->isResetPasswordLinkTokenExpired($rpToken, $rpTokenCreatedAt)) {
            throw new ExpiredException(__('The password token is expired. Reset and try again.'));
        }
    }

    /**
     * Check if customer can be deleted.
     *
     * @param int $customerId
     * @return bool
     * @throws NoSuchEntityException If group is not found
     * @throws LocalizedException
     */
    public function isReadonly(int $customerId): bool
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
     * @param string|null $sendemailStoreId
     * @return $this
     * @throws LocalizedException
     * @deprecated 100.1.0
     * @see EmailNotification::newAccount()
     */
    protected function sendNewAccountEmail(
        CustomerInterface $customer,
        string            $type = self::NEW_ACCOUNT_EMAIL_REGISTERED,
        string            $backUrl = '',
        string            $storeId = '0',
        string            $sendemailStoreId = null
    ): static {
        $types = $this->getTemplateTypes();

        if (!isset($types[$type])) {
            throw new LocalizedException(
                __('The transactional account email type is incorrect. Verify and try again.')
            );
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
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @deprecated 100.1.0
     * @see EmailNotification::credentialsChanged()
     */
    protected function sendPasswordResetNotificationEmail(CustomerInterface $customer): static
    {
        return $this->sendPasswordResetConfirmationEmail($customer);
    }

    /**
     * Get either first store ID from a set website or the provided as default
     *
     * @param CustomerInterface $customer
     * @param int|string|null $defaultStoreId
     * @return int
     * @throws LocalizedException
     *@see StoreManagerInterface::getWebsite()
     * @deprecated 100.1.0
     */
    protected function getWebsiteStoreId(CustomerInterface $customer, int|string $defaultStoreId = null): int|string|null
    {
        if ($customer->getWebsiteId() != 0 && empty($defaultStoreId)) {
            $storeIds = $this->storeManager->getWebsite($customer->getWebsiteId())->getStoreIds();
            reset($storeIds);
            $defaultStoreId = current($storeIds);
        }
        return $defaultStoreId;
    }

    /**
     * Get template types
     *
     * @return array
     * @deprecated 100.1.0
     * @see EmailNotification::TEMPLATE_TYPES
     */
    #[ArrayShape([self::NEW_ACCOUNT_EMAIL_REGISTERED => "string", self::NEW_ACCOUNT_EMAIL_REGISTERED_NO_PASSWORD => "string", self::NEW_ACCOUNT_EMAIL_CONFIRMED => "string", self::NEW_ACCOUNT_EMAIL_CONFIRMATION => "string"])]
    protected function getTemplateTypes(): array
    {
        /**
         * self::NEW_ACCOUNT_EMAIL_REGISTERED               welcome email, when confirmation is disabled
         *                                                  and password is set
         * self::NEW_ACCOUNT_EMAIL_REGISTERED_NO_PASSWORD   welcome email, when confirmation is disabled
         *                                                  and password is not set
         * self::NEW_ACCOUNT_EMAIL_CONFIRMED                welcome email, when confirmation is enabled
         *                                                  and password is set
         * self::NEW_ACCOUNT_EMAIL_CONFIRMATION             email with confirmation link
         */
        $types = [
            self::NEW_ACCOUNT_EMAIL_REGISTERED => self::XML_PATH_REGISTER_EMAIL_TEMPLATE,
            self::NEW_ACCOUNT_EMAIL_REGISTERED_NO_PASSWORD => self::XML_PATH_REGISTER_NO_PASSWORD_EMAIL_TEMPLATE,
            self::NEW_ACCOUNT_EMAIL_CONFIRMED => self::XML_PATH_CONFIRMED_EMAIL_TEMPLATE,
            self::NEW_ACCOUNT_EMAIL_CONFIRMATION => self::XML_PATH_CONFIRM_EMAIL_TEMPLATE,
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
     * @param string|null $email
     * @return $this
     * @throws MailException|LocalizedException
     * @deprecated 100.1.0
     * @see EmailNotification::sendEmailTemplate()
     */
    protected function sendEmailTemplate(
        CustomerInterface $customer,
        string            $template,
        string            $sender,
        array             $templateParams = [],
        int               $storeId = null,
        string $email = null
    ): static {
        $templateId = $this->scopeConfig->getValue(
            $template,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if ($email === null) {
            $email = $customer->getEmail();
        }

        $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
            ->setTemplateOptions(
                [
                    'area' => Area::AREA_FRONTEND,
                    'store' => $storeId
                ]
            )
            ->setTemplateVars($templateParams)
            ->setFrom(
                $this->scopeConfig->getValue(
                    $sender,
                    ScopeInterface::SCOPE_STORE,
                    $storeId
                )
            )
            ->addTo($email, $this->customerViewHelper->getCustomerName($customer))
            ->getTransport();

        $transport->sendMessage();

        return $this;
    }

    /**
     * Check if accounts confirmation is required in config
     *
     * @param CustomerInterface $customer
     * @return bool
     * @deprecated 101.0.4
     * @see AccountConfirmation::isConfirmationRequired
     */
    protected function isConfirmationRequired(CustomerInterface $customer): bool
    {
        return $this->accountConfirmation->isConfirmationRequired(
            $customer->getWebsiteId(),
            $customer->getId(),
            $customer->getEmail()
        );
    }

    /**
     * Check whether confirmation may be skipped when registering using certain email address
     *
     * @param CustomerInterface $customer
     * @return bool
     * @deprecated 101.0.4
     * @see AccountConfirmation::isConfirmationRequired
     */
    protected function canSkipConfirmation(CustomerInterface $customer): bool
    {
        if (!$customer->getId() || $customer->getEmail() === null) {
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
    public function isResetPasswordLinkTokenExpired(string $rpToken, string $rpTokenCreatedAt): bool
    {
        if (empty($rpToken) || empty($rpTokenCreatedAt)) {
            return true;
        }

        $expirationPeriod = $this->customerModel->getResetPasswordLinkExpirationPeriod();

        $currentTimestamp = $this->dateTimeFactory->create()->getTimestamp();
        $tokenTimestamp = $this->dateTimeFactory->create($rpTokenCreatedAt)->getTimestamp();
        if ($tokenTimestamp > $currentTimestamp) {
            return true;
        }

        $hourDifference = floor(($currentTimestamp - $tokenTimestamp) / (60 * 60));
        if ($hourDifference >= $expirationPeriod) {
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
     * @throws InputMismatchException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function changeResetPasswordLinkToken(CustomerInterface $customer, string $passwordLinkToken): bool
    {
        if (!is_string($passwordLinkToken) || empty($passwordLinkToken)) {
            throw new InputException(
                __(
                    'Invalid value of "%value" provided for the %fieldName field.',
                    ['value' => $passwordLinkToken, 'fieldName' => 'password reset token']
                )
            );
        } else {
            $customerSecure = $this->customerRegistry->retrieveSecureData($customer->getId());
            $customerSecure->setRpToken($passwordLinkToken);
            $customerSecure->setRpTokenCreatedAt(
                $this->dateTimeFactory->create()->format(DateTime::DATETIME_PHP_FORMAT)
            );
            $this->setIgnoreValidationFlag($customer);
            $this->customerRepository->save($customer);
        }
        return true;
    }

    /**
     * Send email with new customer password
     *
     * @param CustomerInterface $customer
     * @return $this
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @deprecated 100.1.0
     * @see EmailNotification::passwordReminder()
     */
    public function sendPasswordReminderEmail(CustomerInterface $customer): static
    {
        $storeId = $this->storeManager->getStore()->getId();
        if (!$storeId) {
            $storeId = $this->getWebsiteStoreId($customer);
        }

        $customerEmailData = $this->getFullCustomerObject($customer);

        $this->sendEmailTemplate(
            $customer,
            self::XML_PATH_REMIND_EMAIL_TEMPLATE,
            self::XML_PATH_FORGOT_EMAIL_IDENTITY,
            ['customer' => $customerEmailData, 'store' => $this->storeManager->getStore($storeId)],
            $storeId
        );

        return $this;
    }

    /**
     * Send email with reset password confirmation link
     *
     * @param CustomerInterface $customer
     * @return $this
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @deprecated 100.1.0
     * @see EmailNotification::passwordResetConfirmation()
     */
    public function sendPasswordResetConfirmationEmail(CustomerInterface $customer): static
    {
        $storeId = $this->storeManager->getStore()->getId();
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
    protected function getAddressById(CustomerInterface $customer, int $addressId): ?AddressInterface
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
     * @throws NoSuchEntityException
     * @deprecated 100.1.0
     * @see EmailNotification::getFullCustomerObject()
     */
    protected function getFullCustomerObject(CustomerInterface $customer): Data\CustomerSecure
    {
        // No need to flatten the custom attributes or nested objects since the only usage is for email templates and
        // object passed for events
        $mergedCustomerData = $this->customerRegistry->retrieveSecureData($customer->getId());
        $customerData = $this->dataProcessor->buildOutputDataArray(
            $customer,
            CustomerInterface::class
        );
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
    public function getPasswordHash(string $password): string
    {
        return $this->encryptor->getHash($password, true);
    }

    /**
     * Disable Customer Address Validation
     *
     * @param CustomerInterface $customer
     * @throws NoSuchEntityException
     */
    private function disableAddressValidation(CustomerInterface $customer)
    {
        foreach ($customer->getAddresses() as $address) {
            $addressModel = $this->addressRegistry->retrieve($address->getId());
            $addressModel->setShouldIgnoreValidation(true);
        }
    }

    /**
     * Get email notification
     *
     * @return EmailNotificationInterface
     */
    private function getEmailNotification(): EmailNotificationInterface
    {
        if (!($this->emailNotification instanceof EmailNotificationInterface)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(
                EmailNotificationInterface::class
            );
        } else {
            return $this->emailNotification;
        }
    }

    /**
     * Set ignore_validation_flag for reset password flow to skip unnecessary address and customer validation
     *
     * @param Customer $customer
     * @return void
     */
    private function setIgnoreValidationFlag(Customer $customer)
    {
        $customer->setData('ignore_validation_flag', true);
    }

    /**
     * Check is address allowed for store
     *
     * @param AddressInterface $address
     * @param int|null $storeId
     * @return bool
     */
    private function isAddressAllowedForWebsite(AddressInterface $address, ?int $storeId): bool
    {
        $allowedCountries = $this->allowedCountriesReader->getAllowedCountries(ScopeInterface::SCOPE_STORE, $storeId);

        return in_array($address->getCountryId(), $allowedCountries);
    }
}
