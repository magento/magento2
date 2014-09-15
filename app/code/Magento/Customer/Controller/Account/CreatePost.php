<?php
/**
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Customer\Controller\Account;

use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\InputException;
use Magento\Customer\Service\V1\CustomerAccountServiceInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreatePost extends \Magento\Customer\Controller\Account
{
    /** @var \Magento\Customer\Model\CustomerExtractor */
    protected $customerExtractor;

    /** @var \Magento\Customer\Model\Metadata\FormFactory */
    protected $_formFactory;

    /** @var \Magento\Newsletter\Model\SubscriberFactory */
    protected $_subscriberFactory;

    /** @var \Magento\Customer\Service\V1\Data\RegionBuilder */
    protected $_regionBuilder;

    /** @var \Magento\Customer\Service\V1\Data\AddressBuilder */
    protected $_addressBuilder;

    /** @var \Magento\Customer\Service\V1\Data\CustomerDetailsBuilder */
    protected $_customerDetailsBuilder;

    /** @var \Magento\Customer\Helper\Data */
    protected $_customerHelperData;

    /** @var \Magento\Framework\Escaper */
    protected $escaper;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Helper\Address $addressHelper
     * @param \Magento\Framework\UrlFactory $urlFactory
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param CustomerAccountServiceInterface $customerAccountService
     * @param \Magento\Customer\Model\Metadata\FormFactory $formFactory
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     * @param \Magento\Customer\Service\V1\Data\RegionBuilder $regionBuilder
     * @param \Magento\Customer\Service\V1\Data\AddressBuilder $addressBuilder
     * @param \Magento\Customer\Service\V1\Data\CustomerDetailsBuilder $customerDetailsBuilder
     * @param \Magento\Customer\Helper\Data $customerHelperData
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Customer\Model\CustomerExtractor $customerExtractor
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Helper\Address $addressHelper,
        \Magento\Framework\UrlFactory $urlFactory,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        CustomerAccountServiceInterface $customerAccountService,
        \Magento\Customer\Model\Metadata\FormFactory $formFactory,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        \Magento\Customer\Service\V1\Data\RegionBuilder $regionBuilder,
        \Magento\Customer\Service\V1\Data\AddressBuilder $addressBuilder,
        \Magento\Customer\Service\V1\Data\CustomerDetailsBuilder $customerDetailsBuilder,
        \Magento\Customer\Helper\Data $customerHelperData,
        \Magento\Framework\Escaper $escaper,
        \Magento\Customer\Model\CustomerExtractor $customerExtractor
    ) {
        $this->_formFactory = $formFactory;
        $this->_subscriberFactory = $subscriberFactory;
        $this->_regionBuilder = $regionBuilder;
        $this->_addressBuilder = $addressBuilder;
        $this->_customerDetailsBuilder = $customerDetailsBuilder;
        $this->_customerHelperData = $customerHelperData;
        $this->escaper = $escaper;
        $this->customerExtractor = $customerExtractor;
        parent::__construct(
            $context,
            $customerSession,
            $addressHelper,
            $urlFactory,
            $storeManager,
            $scopeConfig,
            $customerAccountService
        );
    }

    /**
     * Add address to customer during create account
     *
     * @return \Magento\Customer\Service\V1\Data\Address|null
     */
    protected function _extractAddress()
    {
        if (!$this->getRequest()->getPost('create_address')) {
            return null;
        }

        $addressForm = $this->_formFactory->create('customer_address', 'customer_register_address');
        $allowedAttributes = $addressForm->getAllowedAttributes();

        $addressData = array();

        foreach ($allowedAttributes as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            $value = $this->getRequest()->getParam($attributeCode);
            if (is_null($value)) {
                continue;
            }
            switch ($attributeCode) {
                case 'region_id':
                    $this->_regionBuilder->setRegionId($value);
                    break;
                case 'region':
                    $this->_regionBuilder->setRegion($value);
                    break;
                default:
                    $addressData[$attributeCode] = $value;
            }
        }
        $this->_addressBuilder->populateWithArray($addressData);
        $this->_addressBuilder->setRegion($this->_regionBuilder->create());

        $this->_addressBuilder->setDefaultBilling(
            $this->getRequest()->getParam('default_billing', false)
        )->setDefaultShipping(
            $this->getRequest()->getParam('default_shipping', false)
        );
        return $this->_addressBuilder->create();
    }

    /**
     * Is registration allowed
     *
     * @return bool
     */
    protected function isRegistrationAllowed()
    {
        return $this->_customerHelperData->isRegistrationAllowed();
    }

    /**
     * Create customer account action
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        if ($this->_getSession()->isLoggedIn() || !$this->isRegistrationAllowed()) {
            $this->_redirect('*/*/');
            return;
        }

        if (!$this->getRequest()->isPost()) {
            $url = $this->_createUrl()->getUrl('*/*/create', array('_secure' => true));
            $this->getResponse()->setRedirect($this->_redirect->error($url));
            return;
        }

        $this->_session->regenerateId();

        try {
            $customer = $this->customerExtractor->extract('customer_account_create', $this->_request);
            $address = $this->_extractAddress();
            $addresses = is_null($address) ? array() : array($address);
            $password = $this->getRequest()->getParam('password');
            $redirectUrl = $this->_getSession()->getBeforeAuthUrl();
            $customerDetails = $this->_customerDetailsBuilder
                ->setCustomer($customer)
                ->setAddresses($addresses)
                ->create();
            $customer = $this->_customerAccountService->createCustomer($customerDetails, $password, $redirectUrl);

            if ($this->getRequest()->getParam('is_subscribed', false)) {
                $this->_subscriberFactory->create()->subscribeCustomerById($customer->getId());
            }

            $this->_eventManager->dispatch(
                'customer_register_success',
                array('account_controller' => $this, 'customer' => $customer)
            );

            $confirmationStatus = $this->_customerAccountService->getConfirmationStatus($customer->getId());
            if ($confirmationStatus === CustomerAccountServiceInterface::ACCOUNT_CONFIRMATION_REQUIRED) {
                $email = $this->_customerHelperData->getEmailConfirmationUrl($customer->getEmail());
                // @codingStandardsIgnoreStart
                $this->messageManager->addSuccess(
                    __(
                        'Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email please <a href="%1">click here</a>.',
                        $email
                    )
                );
                // @codingStandardsIgnoreEnd
                $url = $this->_createUrl()->getUrl('*/*/index', array('_secure' => true));
                $this->getResponse()->setRedirect($this->_redirect->success($url));
            } else {
                $this->_getSession()->setCustomerDataAsLoggedIn($customer);
                $url = $this->_welcomeCustomer($customer);
                $this->getResponse()->setRedirect($this->_redirect->success($url));
            }
            return;
        } catch (StateException $e) {
            $url = $this->_createUrl()->getUrl('customer/account/forgotpassword');
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
        $defaultUrl = $this->_createUrl()->getUrl('*/*/create', array('_secure' => true));
        $this->getResponse()->setRedirect($this->_redirect->error($defaultUrl));
    }
}
