<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Account;

use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Model\Url;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Helper\Address;
use Magento\Framework\UrlFactory;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Customer\Api\Data\RegionDataBuilder;
use Magento\Customer\Api\Data\AddressDataBuilder;
use Magento\Customer\Api\Data\CustomerDataBuilder;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Customer\Model\Registration;
use Magento\Framework\Escaper;
use Magento\Customer\Model\CustomerExtractor;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\InputException;
use Magento\Store\Model\ScopeInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreatePost extends \Magento\Customer\Controller\Account
{
    /** @var ScopeConfigInterface */
    protected $scopeConfig;

    /** @var StoreManagerInterface */
    protected $storeManager;

    /** @var AccountManagementInterface */
    protected $accountManagement;

    /** @var Address */
    protected $addressHelper;

    /** @var FormFactory */
    protected $formFactory;

    /** @var SubscriberFactory */
    protected $subscriberFactory;

    /** @var RegionDataBuilder */
    protected $regionBuilder;

    /** @var AddressDataBuilder */
    protected $addressBuilder;

    /** @var Registration */
    protected $registration;

    /** @var CustomerDataBuilder */
    protected $customerDataBuilder;

    /** @var CustomerUrl */
    protected $customerUrl;

    /** @var Escaper */
    protected $escaper;

    /** @var CustomerExtractor */
    protected $customerExtractor;

    /** @var \Magento\Framework\UrlInterface */
    protected $urlModel;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param AccountManagementInterface $accountManagement
     * @param Address $addressHelper
     * @param UrlFactory $urlFactory
     * @param FormFactory $formFactory
     * @param SubscriberFactory $subscriberFactory
     * @param RegionDataBuilder $regionBuilder
     * @param AddressDataBuilder $addressBuilder
     * @param CustomerDataBuilder $customerDetailsBuilder
     * @param CustomerUrl $customerUrl
     * @param Registration $registration
     * @param Escaper $escaper
     * @param CustomerExtractor $customerExtractor
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        AccountManagementInterface $accountManagement,
        Address $addressHelper,
        UrlFactory $urlFactory,
        FormFactory $formFactory,
        SubscriberFactory $subscriberFactory,
        RegionDataBuilder $regionBuilder,
        AddressDataBuilder $addressBuilder,
        CustomerDataBuilder $customerDetailsBuilder,
        CustomerUrl $customerUrl,
        Registration $registration,
        Escaper $escaper,
        CustomerExtractor $customerExtractor
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->accountManagement = $accountManagement;
        $this->addressHelper = $addressHelper;
        $this->formFactory = $formFactory;
        $this->subscriberFactory = $subscriberFactory;
        $this->regionBuilder = $regionBuilder;
        $this->addressBuilder = $addressBuilder;
        $this->customerDataBuilder = $customerDetailsBuilder;
        $this->customerUrl = $customerUrl;
        $this->registration = $registration;
        $this->escaper = $escaper;
        $this->customerExtractor = $customerExtractor;
        $this->urlModel = $urlFactory->create();
        parent::__construct($context, $customerSession);
    }

    /**
     * Add address to customer during create account
     *
     * @return AddressInterface|null
     */
    protected function extractAddress()
    {
        if (!$this->getRequest()->getPost('create_address')) {
            return null;
        }

        $addressForm = $this->formFactory->create('customer_address', 'customer_register_address');
        $allowedAttributes = $addressForm->getAllowedAttributes();

        $addressData = [];

        foreach ($allowedAttributes as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            $value = $this->getRequest()->getParam($attributeCode);
            if (is_null($value)) {
                continue;
            }
            switch ($attributeCode) {
                case 'region_id':
                    $this->regionBuilder->setRegionId($value);
                    break;
                case 'region':
                    $this->regionBuilder->setRegion($value);
                    break;
                default:
                    $addressData[$attributeCode] = $value;
            }
        }
        $this->addressBuilder->populateWithArray($addressData);
        $this->addressBuilder->setRegion($this->regionBuilder->create());

        $this->addressBuilder->setDefaultBilling(
            $this->getRequest()->getParam('default_billing', false)
        )->setDefaultShipping(
            $this->getRequest()->getParam('default_shipping', false)
        );
        return $this->addressBuilder->create();
    }

    /**
     * Create customer account action
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        if ($this->_getSession()->isLoggedIn() || !$this->registration->isAllowed()) {
            $this->_redirect('*/*/');
            return;
        }

        if (!$this->getRequest()->isPost()) {
            $url = $this->urlModel->getUrl('*/*/create', ['_secure' => true]);
            $this->getResponse()->setRedirect($this->_redirect->error($url));
            return;
        }

        $this->_getSession()->regenerateId();

        try {
            $address = $this->extractAddress();
            $addresses = is_null($address) ? [] : [$address];

            $customer = $this->customerExtractor->extract('customer_account_create', $this->_request);
            $customer = $this->customerDataBuilder
                ->populate($customer)
                ->setAddresses($addresses)
                ->create();

            $password = $this->getRequest()->getParam('password');
            $confirmation = $this->getRequest()->getParam('password_confirmation');
            $redirectUrl = $this->_getSession()->getBeforeAuthUrl();

            $this->checkPasswordConfirmation($password, $confirmation);

            $customer = $this->accountManagement
                ->createAccount($customer, $password, $redirectUrl);

            if ($this->getRequest()->getParam('is_subscribed', false)) {
                $this->subscriberFactory->create()->subscribeCustomerById($customer->getId());
            }

            $this->_eventManager->dispatch(
                'customer_register_success',
                ['account_controller' => $this, 'customer' => $customer]
            );

            $confirmationStatus = $this->accountManagement->getConfirmationStatus($customer->getId());
            if ($confirmationStatus === AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED) {
                $email = $this->customerUrl->getEmailConfirmationUrl($customer->getEmail());
                // @codingStandardsIgnoreStart
                $this->messageManager->addSuccess(
                    __(
                        'Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email please <a href="%1">click here</a>.',
                        $email
                    )
                );
                // @codingStandardsIgnoreEnd
                $url = $this->urlModel->getUrl('*/*/index', ['_secure' => true]);
                $this->getResponse()->setRedirect($this->_redirect->success($url));
            } else {
                $this->_getSession()->setCustomerDataAsLoggedIn($customer);

                $this->messageManager->addSuccess($this->getSuccessMessage());
                $this->getResponse()->setRedirect($this->getSuccessRedirect());
            }
            return;
        } catch (StateException $e) {
            $url = $this->urlModel->getUrl('customer/account/forgotpassword');
            // @codingStandardsIgnoreStart
            $message = __(
                'There is already an account with this email address. If you are sure that it is your email address, <a href="%1">click here</a> to get your password and access your account.',
                $url
            );
            // @codingStandardsIgnoreEnd
            $this->messageManager->addError($message);
        } catch (InputException $e) {
            $this->messageManager->addError($this->escaper->escapeHtml($e->getMessage()));
            foreach ($e->getErrors() as $error) {
                $this->messageManager->addError($this->escaper->escapeHtml($error->getMessage()));
            }
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Cannot save the customer.'));
        }

        $this->_getSession()->setCustomerFormData($this->getRequest()->getPost());
        $defaultUrl = $this->urlModel->getUrl('*/*/create', ['_secure' => true]);
        $this->getResponse()->setRedirect($this->_redirect->error($defaultUrl));
    }

    /**
     * Make sure that password and password confirmation matched
     *
     * @param string $password
     * @param string $confirmation
     * @return void
     * @throws InputException
     */
    protected function checkPasswordConfirmation($password, $confirmation)
    {
        if ($password != $confirmation) {
            throw new InputException('Please make sure your passwords match.');
        }
    }

    /**
     * Retrieve success message
     *
     * @return string
     */
    protected function getSuccessMessage()
    {
        if ($this->addressHelper->isVatValidationEnabled()) {
            if ($this->addressHelper->getTaxCalculationAddressType() == Address::TYPE_SHIPPING) {
                // @codingStandardsIgnoreStart
                $message = __(
                    'If you are a registered VAT customer, please click <a href="%1">here</a> to enter you shipping address for proper VAT calculation',
                    $this->urlModel->getUrl('customer/address/edit')
                );
                // @codingStandardsIgnoreEnd
            } else {
                // @codingStandardsIgnoreStart
                $message = __(
                    'If you are a registered VAT customer, please click <a href="%1">here</a> to enter you billing address for proper VAT calculation',
                    $this->urlModel->getUrl('customer/address/edit')
                );
                // @codingStandardsIgnoreEnd
            }
        } else {
            $message = __('Thank you for registering with %1.', $this->storeManager->getStore()->getFrontendName());
        }
        return $message;
    }

    /**
     * Retrieve success redirect URL
     *
     * @return string
     */
    protected function getSuccessRedirect()
    {
        $redirectToDashboard = $this->scopeConfig->isSetFlag(
            Url::XML_PATH_CUSTOMER_STARTUP_REDIRECT_TO_DASHBOARD,
            ScopeInterface::SCOPE_STORE
        );
        if (!$redirectToDashboard && $this->_getSession()->getBeforeAuthUrl()) {
            $successUrl = $this->_getSession()->getBeforeAuthUrl(true);
        } else {
            $successUrl = $this->urlModel->getUrl('*/*/index', ['_secure' => true]);
        }
        return $this->_redirect->success($successUrl);
    }
}
