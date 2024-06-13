<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Customer\Helper\View as CustomerViewHelper;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;
use Magento\Customer\Model\Data\CustomerSecure;

/**
 * Customer email notification
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EmailNotification implements EmailNotificationInterface
{
    /**#@+
     * Configuration paths for email templates and identities
     */
    public const XML_PATH_FORGOT_EMAIL_IDENTITY = 'customer/password/forgot_email_identity';

    public const XML_PATH_RESET_PASSWORD_TEMPLATE = 'customer/password/reset_password_template';

    public const XML_PATH_CHANGE_EMAIL_TEMPLATE = 'customer/account_information/change_email_template';

    public const XML_PATH_CHANGE_EMAIL_AND_PASSWORD_TEMPLATE =
        'customer/account_information/change_email_and_password_template';

    public const XML_PATH_FORGOT_EMAIL_TEMPLATE = 'customer/password/forgot_email_template';

    public const XML_PATH_REMIND_EMAIL_TEMPLATE = 'customer/password/remind_email_template';

    public const XML_PATH_REGISTER_EMAIL_IDENTITY = 'customer/create_account/email_identity';

    public const XML_PATH_REGISTER_EMAIL_TEMPLATE = 'customer/create_account/email_template';

    public const XML_PATH_REGISTER_NO_PASSWORD_EMAIL_TEMPLATE = 'customer/create_account/email_no_password_template';

    public const XML_PATH_CONFIRM_EMAIL_TEMPLATE = 'customer/create_account/email_confirmation_template';

    public const XML_PATH_CONFIRMED_EMAIL_TEMPLATE = 'customer/create_account/email_confirmed_template';

    /**
     * self::NEW_ACCOUNT_EMAIL_REGISTERED               welcome email, when confirmation is disabled
     *                                                  and password is set
     * self::NEW_ACCOUNT_EMAIL_REGISTERED_NO_PASSWORD   welcome email, when confirmation is disabled
     *                                                  and password is not set
     * self::NEW_ACCOUNT_EMAIL_CONFIRMED                welcome email, when confirmation is enabled
     *                                                  and password is set
     * self::NEW_ACCOUNT_EMAIL_CONFIRMATION             email with confirmation link
     */
    public const TEMPLATE_TYPES = [
        self::NEW_ACCOUNT_EMAIL_REGISTERED => self::XML_PATH_REGISTER_EMAIL_TEMPLATE,
        self::NEW_ACCOUNT_EMAIL_REGISTERED_NO_PASSWORD => self::XML_PATH_REGISTER_NO_PASSWORD_EMAIL_TEMPLATE,
        self::NEW_ACCOUNT_EMAIL_CONFIRMED => self::XML_PATH_CONFIRMED_EMAIL_TEMPLATE,
        self::NEW_ACCOUNT_EMAIL_CONFIRMATION => self::XML_PATH_CONFIRM_EMAIL_TEMPLATE,
    ];

    /**#@-*/

    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var CustomerViewHelper
     */
    protected $customerViewHelper;

    /**
     * @var DataObjectProcessor
     */
    protected $dataProcessor;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var SenderResolverInterface
     */
    private $senderResolver;

    /**
     * @var Emulation
     */
    private $emulation;

    /**
     * @var AccountConfirmation
     */
    private AccountConfirmation $accountConfirmation;

    /**
     * @param CustomerRegistry $customerRegistry
     * @param StoreManagerInterface $storeManager
     * @param TransportBuilder $transportBuilder
     * @param CustomerViewHelper $customerViewHelper
     * @param DataObjectProcessor $dataProcessor
     * @param ScopeConfigInterface $scopeConfig
     * @param SenderResolverInterface|null $senderResolver
     * @param Emulation|null $emulation
     * @param AccountConfirmation|null $accountConfirmation
     */
    public function __construct(
        CustomerRegistry $customerRegistry,
        StoreManagerInterface $storeManager,
        TransportBuilder $transportBuilder,
        CustomerViewHelper $customerViewHelper,
        DataObjectProcessor $dataProcessor,
        ScopeConfigInterface $scopeConfig,
        ?SenderResolverInterface $senderResolver = null,
        ?Emulation $emulation = null,
        ?AccountConfirmation $accountConfirmation = null
    ) {
        $this->customerRegistry = $customerRegistry;
        $this->storeManager = $storeManager;
        $this->transportBuilder = $transportBuilder;
        $this->customerViewHelper = $customerViewHelper;
        $this->dataProcessor = $dataProcessor;
        $this->scopeConfig = $scopeConfig;
        $this->senderResolver = $senderResolver ?? ObjectManager::getInstance()->get(SenderResolverInterface::class);
        $this->emulation = $emulation ?? ObjectManager::getInstance()->get(Emulation::class);
        $this->accountConfirmation = $accountConfirmation ?? ObjectManager::getInstance()
                ->get(AccountConfirmation::class);
    }

    /**
     * Send notification to customer when email or/and password changed
     *
     * @param CustomerInterface $savedCustomer
     * @param string $origCustomerEmail
     * @param bool $isPasswordChanged
     * @return void
     * @throws LocalizedException
     */
    public function credentialsChanged(
        CustomerInterface $savedCustomer,
        $origCustomerEmail,
        $isPasswordChanged = false
    ): void {
        if ($origCustomerEmail != $savedCustomer->getEmail()) {
            $this->emailChangedConfirmation($savedCustomer);
            if ($isPasswordChanged) {
                $this->emailAndPasswordChanged($savedCustomer, $origCustomerEmail);
                $this->emailAndPasswordChanged($savedCustomer, $savedCustomer->getEmail());
                return;
            }

            $this->emailChanged($savedCustomer, $origCustomerEmail);
            $this->emailChanged($savedCustomer, $savedCustomer->getEmail());
            return;
        }

        if ($isPasswordChanged) {
            $this->passwordReset($savedCustomer);
        }
    }

    /**
     * Send email to customer when his email and password is changed
     *
     * @param CustomerInterface $customer
     * @param string $email
     * @return void
     * @throws MailException
     * @throws NoSuchEntityException|LocalizedException
     */
    private function emailAndPasswordChanged(CustomerInterface $customer, $email): void
    {
        $storeId = $customer->getStoreId();
        if ($storeId === null) {
            $storeId = $this->getWebsiteStoreId($customer);
        }

        $customerEmailData = $this->getFullCustomerObject($customer);

        $this->sendEmailTemplate(
            $customer,
            self::XML_PATH_CHANGE_EMAIL_AND_PASSWORD_TEMPLATE,
            self::XML_PATH_FORGOT_EMAIL_IDENTITY,
            ['customer' => $customerEmailData, 'store' => $this->storeManager->getStore($storeId)],
            $storeId,
            $email
        );
    }

    /**
     * Send email to customer when his email is changed
     *
     * @param CustomerInterface $customer
     * @param string $email
     * @return void
     * @throws MailException
     * @throws NoSuchEntityException|LocalizedException
     */
    private function emailChanged(CustomerInterface $customer, $email): void
    {
        $storeId = $customer->getStoreId();
        if ($storeId === null) {
            $storeId = $this->getWebsiteStoreId($customer);
        }

        $customerEmailData = $this->getFullCustomerObject($customer);

        $this->sendEmailTemplate(
            $customer,
            self::XML_PATH_CHANGE_EMAIL_TEMPLATE,
            self::XML_PATH_FORGOT_EMAIL_IDENTITY,
            ['customer' => $customerEmailData, 'store' => $this->storeManager->getStore($storeId)],
            $storeId,
            $email
        );
    }

    /**
     * Send email to customer when his password is reset
     *
     * @param CustomerInterface $customer
     * @return void
     * @throws MailException
     * @throws NoSuchEntityException|LocalizedException
     */
    private function passwordReset(CustomerInterface $customer): void
    {
        $storeId = $customer->getStoreId();
        if ($storeId === null) {
            $storeId = $this->getWebsiteStoreId($customer);
        }

        $customerEmailData = $this->getFullCustomerObject($customer);

        $this->sendEmailTemplate(
            $customer,
            self::XML_PATH_RESET_PASSWORD_TEMPLATE,
            self::XML_PATH_FORGOT_EMAIL_IDENTITY,
            ['customer' => $customerEmailData, 'store' => $this->storeManager->getStore($storeId)],
            $storeId
        );
    }

    /**
     * Send corresponding email template
     *
     * @param CustomerInterface $customer
     * @param string $template configuration path of email template
     * @param string $sender configuration path of email identity
     * @param array $templateParams
     * @param int|null $storeId
     * @param string $email
     * @return void
     * @throws MailException|LocalizedException
     */
    private function sendEmailTemplate(
        $customer,
        $template,
        $sender,
        $templateParams = [],
        $storeId = null,
        $email = null
    ): void {
        $templateId = $this->scopeConfig->getValue($template, ScopeInterface::SCOPE_STORE, $storeId);
        if ($email === null) {
            $email = $customer->getEmail();
        }

        /** @var array $from */
        $from = $this->senderResolver->resolve(
            $this->scopeConfig->getValue($sender, ScopeInterface::SCOPE_STORE, $storeId),
            $storeId
        );

        $this->emulation->startEnvironmentEmulation($storeId, \Magento\Framework\App\Area::AREA_FRONTEND);
        $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
            ->setTemplateOptions(['area' => 'frontend', 'store' => $storeId])
            ->setTemplateVars($templateParams)
            ->setFrom($from)
            ->addTo($email, $this->customerViewHelper->getCustomerName($customer))
            ->getTransport();

        $transport->sendMessage();
        $this->emulation->stopEnvironmentEmulation();
    }

    /**
     * Create an object with data merged from Customer and CustomerSecure
     *
     * @param CustomerInterface $customer
     * @return CustomerSecure
     * @throws NoSuchEntityException
     */
    private function getFullCustomerObject($customer): CustomerSecure
    {
        // No need to flatten the custom attributes or nested objects since the only usage is for email templates and
        // object passed for events
        $mergedCustomerData = $this->customerRegistry->retrieveSecureData($customer->getId());
        $customerData = $this->dataProcessor
            ->buildOutputDataArray($customer, CustomerInterface::class);
        $mergedCustomerData->addData($customerData);
        $mergedCustomerData->setData('name', $this->customerViewHelper->getCustomerName($customer));
        return $mergedCustomerData;
    }

    /**
     * Get either first store ID from a set website or the provided as default
     *
     * @param CustomerInterface $customer
     * @param int|string|null $defaultStoreId
     * @return int
     * @throws LocalizedException
     */
    private function getWebsiteStoreId($customer, $defaultStoreId = null): int
    {
        if ($customer->getWebsiteId() != 0 && empty($defaultStoreId)) {
            $storeIds = $this->storeManager->getWebsite($customer->getWebsiteId())->getStoreIds();
            $defaultStoreId = reset($storeIds);
        }
        return $defaultStoreId;
    }

    /**
     * Send email with new customer password
     *
     * @param CustomerInterface $customer
     * @return void
     * @throws LocalizedException
     * @throws MailException
     * @throws NoSuchEntityException
     */
    public function passwordReminder(CustomerInterface $customer): void
    {
        $storeId = $customer->getStoreId();
        if ($storeId === null) {
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
    }

    /**
     * Send email with reset password confirmation link
     *
     * @param CustomerInterface $customer
     * @return void
     * @throws LocalizedException
     * @throws MailException
     * @throws NoSuchEntityException
     */
    public function passwordResetConfirmation(CustomerInterface $customer): void
    {
        $storeId = $customer->getStoreId();
        if ($storeId === null) {
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
    }

    /**
     * Send email with new account related information
     *
     * @param CustomerInterface $customer
     * @param string $type
     * @param string $backUrl
     * @param int|null $storeId
     * @param string $sendemailStoreId
     * @return void
     * @throws LocalizedException
     */
    public function newAccount(
        CustomerInterface $customer,
        $type = self::NEW_ACCOUNT_EMAIL_REGISTERED,
        $backUrl = '',
        $storeId = null,
        $sendemailStoreId = null
    ): void {
        $types = self::TEMPLATE_TYPES;

        if (!isset($types[$type])) {
            throw new LocalizedException(
                __('The transactional account email type is incorrect. Verify and try again.')
            );
        }

        if ($storeId === null) {
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
    }

    /**
     * Sending an email to confirm the email address in case the email address has been changed
     *
     * @param CustomerInterface $customer
     * @throws LocalizedException
     */
    private function emailChangedConfirmation(CustomerInterface $customer): void
    {
        if (!$this->accountConfirmation->isCustomerEmailChangedConfirmRequired($customer)) {
            return;
        }
        $this->newAccount($customer, self::NEW_ACCOUNT_EMAIL_CONFIRMATION, null, $customer->getStoreId());
    }
}
