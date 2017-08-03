<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\Config\Share;
use Magento\Customer\Model\ResourceModel\Address\CollectionFactory;
use Magento\Customer\Model\ResourceModel\Customer as ResourceCustomer;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Indexer\StateInterface;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\ScopeInterface;

/**
 * Customer model
 *
 * @api
 * @method int getWebsiteId() getWebsiteId()
 * @method Customer setWebsiteId($value)
 * @method int getStoreId() getStoreId()
 * @method string getEmail() getEmail()
 * @method ResourceCustomer _getResource()
 * @method mixed getDisableAutoGroupChange()
 * @method Customer setDisableAutoGroupChange($value)
 * @method Customer setGroupId($value)
 * @method Customer setDefaultBilling($value)
 * @method Customer setDefaultShipping($value)
 * @method Customer setPasswordHash($string)
 * @method string getPasswordHash()
 * @method string getConfirmation()
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class Customer extends \Magento\Framework\Model\AbstractModel
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

    const XML_PATH_GENERATE_HUMAN_FRIENDLY_ID = 'customer/create_account/generate_human_friendly_id';

    const SUBSCRIBED_YES = 'yes';

    const SUBSCRIBED_NO = 'no';

    const ENTITY = 'customer';

    const CUSTOMER_GRID_INDEXER_ID = 'customer_grid';

    /**
     * Configuration path to expiration period of reset password link
     */
    const XML_PATH_CUSTOMER_RESET_PASSWORD_LINK_EXPIRATION_PERIOD = 'customer/password/reset_link_expiration_period';

    /**
     * Model event prefix
     *
     * @var string
     * @since 2.0.0
     */
    protected $_eventPrefix = 'customer';

    /**
     * Name of the event object
     *
     * @var string
     * @since 2.0.0
     */
    protected $_eventObject = 'customer';

    /**
     * List of errors
     *
     * @var array
     * @since 2.0.0
     */
    protected $_errors = [];

    /**
     * Assoc array of customer attributes
     *
     * @var array
     * @since 2.0.0
     */
    protected $_attributes;

    /**
     * Customer addresses collection
     *
     * @var \Magento\Customer\Model\ResourceModel\Address\Collection
     * @since 2.0.0
     */
    protected $_addressesCollection;

    /**
     * Is model deletable
     *
     * @var boolean
     * @since 2.0.0
     */
    protected $_isDeleteable = true;

    /**
     * Is model readonly
     *
     * @var boolean
     * @since 2.0.0
     */
    protected $_isReadonly = false;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $_storeManager;

    /**
     * @var \Magento\Eav\Model\Config
     * @since 2.0.0
     */
    protected $_config;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @since 2.0.0
     */
    protected $_scopeConfig;

    /**
     * @var Share
     * @since 2.0.0
     */
    protected $_configShare;

    /**
     * @var AddressFactory
     * @since 2.0.0
     */
    protected $_addressFactory;

    /**
     * @var CollectionFactory
     * @since 2.0.0
     */
    protected $_addressesFactory;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     * @since 2.0.0
     */
    protected $_transportBuilder;

    /**
     * @var GroupRepositoryInterface
     * @since 2.0.0
     */
    protected $_groupRepository;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     * @since 2.0.0
     */
    protected $_encryptor;

    /**
     * @var \Magento\Framework\Math\Random
     * @since 2.0.0
     */
    protected $mathRandom;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     * @since 2.0.0
     */
    protected $dateTime;

    /**
     * @var CustomerInterfaceFactory
     * @since 2.0.0
     */
    protected $customerDataFactory;

    /**
     * @var DataObjectProcessor
     * @since 2.0.0
     */
    protected $dataObjectProcessor;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     * @since 2.0.0
     */
    protected $dataObjectHelper;

    /**
     * @var \Magento\Customer\Api\CustomerMetadataInterface
     * @since 2.0.0
     */
    protected $metadataService;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     * @since 2.0.0
     */
    protected $indexerRegistry;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Eav\Model\Config $config
     * @param ScopeConfigInterface $scopeConfig
     * @param ResourceCustomer $resource
     * @param Share $configShare
     * @param AddressFactory $addressFactory
     * @param CollectionFactory $addressesFactory
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param GroupRepositoryInterface $groupRepository
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param CustomerInterfaceFactory $customerDataFactory
     * @param DataObjectProcessor $dataObjectProcessor
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param CustomerMetadataInterface $metadataService
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Eav\Model\Config $config,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Model\ResourceModel\Customer $resource,
        \Magento\Customer\Model\Config\Share $configShare,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Customer\Model\ResourceModel\Address\CollectionFactory $addressesFactory,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        GroupRepositoryInterface $groupRepository,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        CustomerInterfaceFactory $customerDataFactory,
        DataObjectProcessor $dataObjectProcessor,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Customer\Api\CustomerMetadataInterface $metadataService,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->metadataService = $metadataService;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_config = $config;
        $this->_configShare = $configShare;
        $this->_addressFactory = $addressFactory;
        $this->_addressesFactory = $addressesFactory;
        $this->_transportBuilder = $transportBuilder;
        $this->_groupRepository = $groupRepository;
        $this->_encryptor = $encryptor;
        $this->dateTime = $dateTime;
        $this->customerDataFactory = $customerDataFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->indexerRegistry = $indexerRegistry;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Initialize customer model
     *
     * @return void
     * @since 2.0.0
     */
    public function _construct()
    {
        $this->_init(\Magento\Customer\Model\ResourceModel\Customer::class);
    }

    /**
     * Retrieve customer model with customer data
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @since 2.0.0
     */
    public function getDataModel()
    {
        $customerData = $this->getData();
        $addressesData = [];
        /** @var \Magento\Customer\Model\Address $address */
        foreach ($this->getAddresses() as $address) {
            $addressesData[] = $address->getDataModel();
        }
        $customerDataObject = $this->customerDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $customerDataObject,
            $customerData,
            \Magento\Customer\Api\Data\CustomerInterface::class
        );
        $customerDataObject->setAddresses($addressesData)
            ->setId($this->getId());
        return $customerDataObject;
    }

    /**
     * Update customer data
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return $this
     * @since 2.0.0
     */
    public function updateData($customer)
    {
        $customerDataAttributes = $this->dataObjectProcessor->buildOutputDataArray(
            $customer,
            \Magento\Customer\Api\Data\CustomerInterface::class
        );

        foreach ($customerDataAttributes as $attributeCode => $attributeData) {
            if ($attributeCode == 'password') {
                continue;
            }
            $this->setDataUsingMethod($attributeCode, $attributeData);
        }

        $customAttributes = $customer->getCustomAttributes();
        if ($customAttributes !== null) {
            foreach ($customAttributes as $attribute) {
                $this->setData($attribute->getAttributeCode(), $attribute->getValue());
            }
        }

        $customerId = $customer->getId();
        if ($customerId) {
            $this->setId($customerId);
        }

        // Need to use attribute set or future updates can cause data loss
        if (!$this->getAttributeSetId()) {
            $this->setAttributeSetId(
                CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER
            );
        }

        return $this;
    }

    /**
     * Retrieve customer sharing configuration model
     *
     * @return Share
     * @since 2.0.0
     */
    public function getSharingConfig()
    {
        return $this->_configShare;
    }

    /**
     * Authenticate customer
     *
     * @param  string $login
     * @param  string $password
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * Use \Magento\Customer\Api\AccountManagementInterface::authenticate
     * @since 2.0.0
     */
    public function authenticate($login, $password)
    {
        $this->loadByEmail($login);
        if ($this->getConfirmation() && $this->isConfirmationRequired()) {
            throw new EmailNotConfirmedException(
                __('This account is not confirmed.')
            );
        }
        if (!$this->validatePassword($password)) {
            throw new InvalidEmailOrPasswordException(
                __('Invalid login or password.')
            );
        }
        $this->_eventManager->dispatch(
            'customer_customer_authenticated',
            ['model' => $this, 'password' => $password]
        );

        return true;
    }

    /**
     * Load customer by email
     *
     * @param   string $customerEmail
     * @return  $this
     * @since 2.0.0
     */
    public function loadByEmail($customerEmail)
    {
        $this->_getResource()->loadByEmail($this, $customerEmail);
        return $this;
    }

    /**
     * Change customer password
     *
     * @param   string $newPassword
     * @return  $this
     * @since 2.0.0
     */
    public function changePassword($newPassword)
    {
        $this->_getResource()->changePassword($this, $newPassword);
        return $this;
    }

    /**
     * Get full customer name
     *
     * @return string
     * @since 2.0.0
     */
    public function getName()
    {
        $name = '';

        if ($this->_config->getAttribute('customer', 'prefix')->getIsVisible() && $this->getPrefix()) {
            $name .= $this->getPrefix() . ' ';
        }
        $name .= $this->getFirstname();
        if ($this->_config->getAttribute('customer', 'middlename')->getIsVisible() && $this->getMiddlename()) {
            $name .= ' ' . $this->getMiddlename();
        }
        $name .= ' ' . $this->getLastname();
        if ($this->_config->getAttribute('customer', 'suffix')->getIsVisible() && $this->getSuffix()) {
            $name .= ' ' . $this->getSuffix();
        }
        return $name;
    }

    /**
     * Add address to address collection
     *
     * @param   Address $address
     * @return  $this
     * @since 2.0.0
     */
    public function addAddress(Address $address)
    {
        $this->getAddressesCollection()->addItem($address);
        return $this;
    }

    /**
     * Retrieve customer address by address id
     *
     * @param   int $addressId
     * @return  Address
     * @since 2.0.0
     */
    public function getAddressById($addressId)
    {
        return $this->_createAddressInstance()->load($addressId);
    }

    /**
     * Getting customer address object from collection by identifier
     *
     * @param int $addressId
     * @return Address
     * @since 2.0.0
     */
    public function getAddressItemById($addressId)
    {
        return $this->getAddressesCollection()->getItemById($addressId);
    }

    /**
     * Retrieve not loaded address collection
     *
     * @return \Magento\Customer\Model\ResourceModel\Address\Collection
     * @since 2.0.0
     */
    public function getAddressCollection()
    {
        return $this->_createAddressCollection();
    }

    /**
     * Customer addresses collection
     *
     * @return \Magento\Customer\Model\ResourceModel\Address\Collection
     * @since 2.0.0
     */
    public function getAddressesCollection()
    {
        if ($this->_addressesCollection === null) {
            $this->_addressesCollection = $this->getAddressCollection()->setCustomerFilter(
                $this
            )->addAttributeToSelect(
                '*'
            );
            foreach ($this->_addressesCollection as $address) {
                $address->setCustomer($this);
            }
        }

        return $this->_addressesCollection;
    }

    /**
     * Retrieve customer address array
     *
     * @return \Magento\Framework\DataObject[]
     * @since 2.0.0
     */
    public function getAddresses()
    {
        return $this->getAddressesCollection()->getItems();
    }

    /**
     * Retrieve all customer attributes
     *
     * @return Attribute[]
     * @since 2.0.0
     */
    public function getAttributes()
    {
        if ($this->_attributes === null) {
            $this->_attributes = $this->_getResource()->loadAllAttributes($this)->getSortedAttributes();
        }
        return $this->_attributes;
    }

    /**
     * Get customer attribute model object
     *
     * @param   string $attributeCode
     * @return  \Magento\Customer\Model\ResourceModel\Attribute | null
     * @since 2.0.0
     */
    public function getAttribute($attributeCode)
    {
        $this->getAttributes();
        if (isset($this->_attributes[$attributeCode])) {
            return $this->_attributes[$attributeCode];
        }
        return null;
    }

    /**
     * Set plain and hashed password
     *
     * @param string $password
     * @return $this
     * @since 2.0.0
     */
    public function setPassword($password)
    {
        $this->setData('password', $password);
        $this->setPasswordHash($this->hashPassword($password));
        return $this;
    }

    /**
     * Hash customer password
     *
     * @param string $password
     * @param bool|int|string $salt
     * @return string
     * @since 2.0.0
     */
    public function hashPassword($password, $salt = true)
    {
        return $this->_encryptor->getHash($password, $salt);
    }

    /**
     * Validate password with salted hash
     *
     * @param string $password
     * @return boolean
     * @since 2.0.0
     */
    public function validatePassword($password)
    {
        $hash = $this->getPasswordHash();
        if (!$hash) {
            return false;
        }
        return $this->_encryptor->validateHash($password, $hash);
    }

    /**
     * Encrypt password
     *
     * @param   string $password
     * @return  string
     * @since 2.0.0
     */
    public function encryptPassword($password)
    {
        return $this->_encryptor->encrypt($password);
    }

    /**
     * Decrypt password
     *
     * @param   string $password
     * @return  string
     * @since 2.0.0
     */
    public function decryptPassword($password)
    {
        return $this->_encryptor->decrypt($password);
    }

    /**
     * Retrieve default address by type(attribute)
     *
     * @param   string $attributeCode address type attribute code
     * @return  Address|false
     * @since 2.0.0
     */
    public function getPrimaryAddress($attributeCode)
    {
        $primaryAddress = $this->getAddressesCollection()->getItemById($this->getData($attributeCode));

        return $primaryAddress ? $primaryAddress : false;
    }

    /**
     * Get customer default billing address
     *
     * @return Address
     * @since 2.0.0
     */
    public function getPrimaryBillingAddress()
    {
        return $this->getPrimaryAddress('default_billing');
    }

    /**
     * Get customer default billing address
     *
     * @return Address
     * @since 2.0.0
     */
    public function getDefaultBillingAddress()
    {
        return $this->getPrimaryBillingAddress();
    }

    /**
     * Get default customer shipping address
     *
     * @return Address
     * @since 2.0.0
     */
    public function getPrimaryShippingAddress()
    {
        return $this->getPrimaryAddress('default_shipping');
    }

    /**
     * Get default customer shipping address
     *
     * @return Address
     * @since 2.0.0
     */
    public function getDefaultShippingAddress()
    {
        return $this->getPrimaryShippingAddress();
    }

    /**
     * Retrieve ids of default addresses
     *
     * @return array
     * @since 2.0.0
     */
    public function getPrimaryAddressIds()
    {
        $ids = [];
        if ($this->getDefaultBilling()) {
            $ids[] = $this->getDefaultBilling();
        }
        if ($this->getDefaultShipping()) {
            $ids[] = $this->getDefaultShipping();
        }
        return $ids;
    }

    /**
     * Retrieve all customer default addresses
     *
     * @return Address[]
     * @since 2.0.0
     */
    public function getPrimaryAddresses()
    {
        $addresses = [];
        $primaryBilling = $this->getPrimaryBillingAddress();
        if ($primaryBilling) {
            $addresses[] = $primaryBilling;
            $primaryBilling->setIsPrimaryBilling(true);
        }

        $primaryShipping = $this->getPrimaryShippingAddress();
        if ($primaryShipping) {
            if ($primaryBilling && $primaryBilling->getId() == $primaryShipping->getId()) {
                $primaryBilling->setIsPrimaryShipping(true);
            } else {
                $primaryShipping->setIsPrimaryShipping(true);
                $addresses[] = $primaryShipping;
            }
        }
        return $addresses;
    }

    /**
     * Retrieve not default addresses
     *
     * @return Address[]
     * @since 2.0.0
     */
    public function getAdditionalAddresses()
    {
        $addresses = [];
        $primatyIds = $this->getPrimaryAddressIds();
        foreach ($this->getAddressesCollection() as $address) {
            if (!in_array($address->getId(), $primatyIds)) {
                $addresses[] = $address;
            }
        }
        return $addresses;
    }

    /**
     * Check if address is primary
     *
     * @param Address $address
     * @return boolean
     * @since 2.0.0
     */
    public function isAddressPrimary(Address $address)
    {
        if (!$address->getId()) {
            return false;
        }
        return $address->getId() == $this->getDefaultBilling() || $address->getId() == $this->getDefaultShipping();
    }

    /**
     * Send email with new account related information
     *
     * @param string $type
     * @param string $backUrl
     * @param string $storeId
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function sendNewAccountEmail($type = 'registered', $backUrl = '', $storeId = '0')
    {
        $types = $this->getTemplateTypes();

        if (!isset($types[$type])) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Please correct the transactional account email type.')
            );
        }

        if (!$storeId) {
            $storeId = $this->_getWebsiteStoreId($this->getSendemailStoreId());
        }

        $this->_sendEmailTemplate(
            $types[$type],
            self::XML_PATH_REGISTER_EMAIL_IDENTITY,
            ['customer' => $this, 'back_url' => $backUrl, 'store' => $this->getStore()],
            $storeId
        );

        return $this;
    }

    /**
     * Check if accounts confirmation is required in config
     *
     * @return bool
     * @since 2.0.0
     */
    public function isConfirmationRequired()
    {
        if ($this->canSkipConfirmation()) {
            return false;
        }

        $websiteId = $this->getWebsiteId() ? $this->getWebsiteId() : null;

        return (bool)$this->_scopeConfig->getValue(
            self::XML_PATH_IS_CONFIRM,
            ScopeInterface::SCOPE_WEBSITES,
            $websiteId
        );
    }

    /**
     * Generate random confirmation key
     *
     * @return string
     * @since 2.0.0
     */
    public function getRandomConfirmationKey()
    {
        return md5(uniqid());
    }

    /**
     * Send email with new customer password
     *
     * @return $this
     * @since 2.0.0
     */
    public function sendPasswordReminderEmail()
    {
        $this->_sendEmailTemplate(
            self::XML_PATH_REMIND_EMAIL_TEMPLATE,
            self::XML_PATH_FORGOT_EMAIL_IDENTITY,
            ['customer' => $this, 'store' => $this->getStore()],
            $this->getStoreId()
        );

        return $this;
    }

    /**
     * Send corresponding email template
     *
     * @param string $template configuration path of email template
     * @param string $sender configuration path of email identity
     * @param array $templateParams
     * @param int|null $storeId
     * @return $this
     * @since 2.0.0
     */
    protected function _sendEmailTemplate($template, $sender, $templateParams = [], $storeId = null)
    {
        /** @var \Magento\Framework\Mail\TransportInterface $transport */
        $transport = $this->_transportBuilder->setTemplateIdentifier(
            $this->_scopeConfig->getValue($template, ScopeInterface::SCOPE_STORE, $storeId)
        )->setTemplateOptions(
            ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId]
        )->setTemplateVars(
            $templateParams
        )->setFrom(
            $this->_scopeConfig->getValue($sender, ScopeInterface::SCOPE_STORE, $storeId)
        )->addTo(
            $this->getEmail(),
            $this->getName()
        )->getTransport();
        $transport->sendMessage();

        return $this;
    }

    /**
     * Send email with reset password confirmation link
     *
     * @return $this
     * @since 2.0.0
     */
    public function sendPasswordResetConfirmationEmail()
    {
        $storeId = $this->getStoreId();
        if (!$storeId) {
            $storeId = $this->_getWebsiteStoreId();
        }

        $this->_sendEmailTemplate(
            self::XML_PATH_FORGOT_EMAIL_TEMPLATE,
            self::XML_PATH_FORGOT_EMAIL_IDENTITY,
            ['customer' => $this, 'store' => $this->getStore()],
            $storeId
        );

        return $this;
    }

    /**
     * Retrieve customer group identifier
     *
     * @return int
     * @since 2.0.0
     */
    public function getGroupId()
    {
        if (!$this->hasData('group_id')) {
            $storeId = $this->getStoreId() ? $this->getStoreId() : $this->_storeManager->getStore()->getId();
            $groupId = $this->_scopeConfig->getValue(
                GroupManagement::XML_PATH_DEFAULT_ID,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
            $this->setData('group_id', $groupId);
        }
        return $this->getData('group_id');
    }

    /**
     * Retrieve customer tax class identifier
     *
     * @return int
     * @since 2.0.0
     */
    public function getTaxClassId()
    {
        if (!$this->getData('tax_class_id')) {
            $groupTaxClassId = $this->_groupRepository->getById($this->getGroupId())->getTaxClassId();
            $this->setData('tax_class_id', $groupTaxClassId);
        }
        return $this->getData('tax_class_id');
    }

    /**
     * Retrieve store where customer was created
     *
     * @return \Magento\Store\Model\Store
     * @since 2.0.0
     */
    public function getStore()
    {
        return $this->_storeManager->getStore($this->getStoreId());
    }

    /**
     * Retrieve shared store ids
     *
     * @return array
     * @since 2.0.0
     */
    public function getSharedStoreIds()
    {
        $ids = $this->_getData('shared_store_ids');
        if ($ids === null) {
            $ids = [];
            if ((bool)$this->getSharingConfig()->isWebsiteScope()) {
                $ids = $this->_storeManager->getWebsite($this->getWebsiteId())->getStoreIds();
            } else {
                foreach ($this->_storeManager->getStores() as $store) {
                    $ids[] = $store->getId();
                }
            }
            $this->setData('shared_store_ids', $ids);
        }

        return $ids;
    }

    /**
     * Retrieve shared website ids
     *
     * @return int[]
     * @since 2.0.0
     */
    public function getSharedWebsiteIds()
    {
        $ids = $this->_getData('shared_website_ids');
        if ($ids === null) {
            $ids = [];
            if ((bool)$this->getSharingConfig()->isWebsiteScope()) {
                $ids[] = $this->getWebsiteId();
            } else {
                foreach ($this->_storeManager->getWebsites() as $website) {
                    $ids[] = $website->getId();
                }
            }
            $this->setData('shared_website_ids', $ids);
        }
        return $ids;
    }

    /**
     * Set store to customer
     *
     * @param \Magento\Store\Model\Store $store
     * @return $this
     * @since 2.0.0
     */
    public function setStore(\Magento\Store\Model\Store $store)
    {
        $this->setStoreId($store->getId());
        $this->setWebsiteId($store->getWebsite()->getId());
        return $this;
    }

    /**
     * Validate customer attribute values.
     *
     * @deprecated 2.1.0
     * @return bool
     * @since 2.0.0
     */
    public function validate()
    {
        return true;
    }

    /**
     * Unset subscription
     *
     * @return $this
     * @since 2.0.0
     */
    public function unsetSubscription()
    {
        if (isset($this->_isSubscribed)) {
            unset($this->_isSubscribed);
        }
        return $this;
    }

    /**
     * Clean all addresses
     *
     * @return void
     * @since 2.0.0
     */
    public function cleanAllAddresses()
    {
        $this->_addressesCollection = null;
    }

    /**
     * Add error
     *
     * @param mixed $error
     * @return $this
     * @since 2.0.0
     */
    public function addError($error)
    {
        $this->_errors[] = $error;
        return $this;
    }

    /**
     * Retrieve errors
     *
     * @return array
     * @since 2.0.0
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * Reset errors array
     *
     * @return $this
     * @since 2.0.0
     */
    public function resetErrors()
    {
        $this->_errors = [];
        return $this;
    }

    /**
     * Prepare customer for delete
     *
     * @return $this
     * @since 2.0.0
     */
    public function beforeDelete()
    {
        //TODO : Revisit and figure handling permissions in MAGETWO-11084 Implementation: Service Context Provider
        return parent::beforeDelete();
    }

    /**
     * Processing object after save data
     *
     * @return $this
     * @since 2.0.0
     */
    public function afterSave()
    {
        $indexer = $this->indexerRegistry->get(self::CUSTOMER_GRID_INDEXER_ID);
        if ($indexer->getState()->getStatus() == StateInterface::STATUS_VALID) {
            $this->_getResource()->addCommitCallback([$this, 'reindex']);
        }
        return parent::afterSave();
    }

    /**
     * Init indexing process after customer delete
     *
     * @return \Magento\Framework\Model\AbstractModel
     * @since 2.0.0
     */
    public function afterDeleteCommit()
    {
        $this->reindex();
        return parent::afterDeleteCommit();
    }

    /**
     * Init indexing process after customer save
     *
     * @return void
     * @since 2.0.0
     */
    public function reindex()
    {
        /** @var \Magento\Framework\Indexer\IndexerInterface $indexer */
        $indexer = $this->indexerRegistry->get(self::CUSTOMER_GRID_INDEXER_ID);
        $indexer->reindexRow($this->getId());
    }

    /**
     * Get customer created at date timestamp
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getCreatedAtTimestamp()
    {
        $date = $this->getCreatedAt();
        if ($date) {
            return (new \DateTime($date))->getTimestamp();
        }
        return null;
    }

    /**
     * Reset all model data
     *
     * @return $this
     * @since 2.0.0
     */
    public function reset()
    {
        $this->setData([]);
        $this->setOrigData();
        $this->_attributes = null;

        return $this;
    }

    /**
     * Checks model is deletable
     *
     * @return boolean
     * @since 2.0.0
     */
    public function isDeleteable()
    {
        return $this->_isDeleteable;
    }

    /**
     * Set is deletable flag
     *
     * @param boolean $value
     * @return $this
     * @since 2.0.0
     */
    public function setIsDeleteable($value)
    {
        $this->_isDeleteable = (bool)$value;
        return $this;
    }

    /**
     * Checks model is readonly
     *
     * @return boolean
     * @since 2.0.0
     */
    public function isReadonly()
    {
        return $this->_isReadonly;
    }

    /**
     * Set is readonly flag
     *
     * @param boolean $value
     * @return $this
     * @since 2.0.0
     */
    public function setIsReadonly($value)
    {
        $this->_isReadonly = (bool)$value;
        return $this;
    }

    /**
     * Check whether confirmation may be skipped when registering using certain email address
     *
     * @return bool
     * @since 2.0.0
     */
    protected function canSkipConfirmation()
    {
        if (!$this->getId()) {
            return false;
        }

        /* If an email was used to start the registration process and it is the same email as the one
           used to register, then this can skip confirmation.
           */
        $skipConfirmationIfEmail = $this->_registry->registry("skip_confirmation_if_email");
        if (!$skipConfirmationIfEmail) {
            return false;
        }

        return strtolower($skipConfirmationIfEmail) === strtolower($this->getEmail());
    }

    /**
     * Clone current object
     *
     * @return void
     * @since 2.0.0
     */
    public function __clone()
    {
        $newAddressCollection = $this->getPrimaryAddresses();
        $newAddressCollection = array_merge($newAddressCollection, $this->getAdditionalAddresses());
        $this->setId(null);
        $this->cleanAllAddresses();
        foreach ($newAddressCollection as $address) {
            $this->addAddress(clone $address);
        }
    }

    /**
     * Return Entity Type instance
     *
     * @return \Magento\Eav\Model\Entity\Type
     * @since 2.0.0
     */
    public function getEntityType()
    {
        return $this->_getResource()->getEntityType();
    }

    /**
     * Get either first store ID from a set website or the provided as default
     *
     * @param int|string|null $defaultStoreId
     *
     * @return int
     * @since 2.0.0
     */
    protected function _getWebsiteStoreId($defaultStoreId = null)
    {
        if ($this->getWebsiteId() != 0 && empty($defaultStoreId)) {
            $storeIds = $this->_storeManager->getWebsite($this->getWebsiteId())->getStoreIds();
            reset($storeIds);
            $defaultStoreId = current($storeIds);
        }
        return $defaultStoreId;
    }

    /**
     * Change reset password link token
     *
     * Stores new reset password link token
     *
     * @param string $passwordLinkToken
     * @return $this
     * @throws \Magento\Framework\Exception\AuthenticationException
     * @since 2.0.0
     */
    public function changeResetPasswordLinkToken($passwordLinkToken)
    {
        if (!is_string($passwordLinkToken) || empty($passwordLinkToken)) {
            throw new AuthenticationException(
                __('Please enter a valid password reset token.')
            );
        }
        $this->_getResource()->changeResetPasswordLinkToken($this, $passwordLinkToken);
        return $this;
    }

    /**
     * Check if current reset password link token is expired
     *
     * @return boolean
     * @since 2.0.0
     */
    public function isResetPasswordLinkTokenExpired()
    {
        $linkToken = $this->getRpToken();
        $linkTokenCreatedAt = $this->getRpTokenCreatedAt();

        if (empty($linkToken) || empty($linkTokenCreatedAt)) {
            return true;
        }

        $expirationPeriod = $this->getResetPasswordLinkExpirationPeriod();

        $currentTimestamp = (new \DateTime())->getTimestamp();
        $tokenTimestamp = (new \DateTime($linkTokenCreatedAt))->getTimestamp();
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
     * Retrieve customer reset password link expiration period in days
     *
     * @return int
     * @since 2.0.0
     */
    public function getResetPasswordLinkExpirationPeriod()
    {
        return (int)$this->_scopeConfig->getValue(
            self::XML_PATH_CUSTOMER_RESET_PASSWORD_LINK_EXPIRATION_PERIOD,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
    }

    /**
     * @return Address
     * @since 2.0.0
     */
    protected function _createAddressInstance()
    {
        return $this->_addressFactory->create();
    }

    /**
     * @return \Magento\Customer\Model\ResourceModel\Address\Collection
     * @since 2.0.0
     */
    protected function _createAddressCollection()
    {
        return $this->_addressesFactory->create();
    }

    /**
     * @return array
     * @since 2.0.0
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
     * Check if customer is locked
     *
     * @return boolean
     * @since 2.1.0
     */
    public function isCustomerLocked()
    {
        if ($this->getLockExpires()) {
            $lockExpires = new \DateTime($this->getLockExpires());
            if ($lockExpires > new \DateTime()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Return Password Confirmation
     *
     * @return string
     * @since 2.1.0
     */
    public function getPasswordConfirm()
    {
        return (string) $this->getData('password_confirm');
    }

    /**
     * Return Password
     *
     * @return string
     * @since 2.1.0
     */
    public function getPassword()
    {
        return (string) $this->getData('password');
    }
}
