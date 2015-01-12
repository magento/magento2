<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\Config\Share;
use Magento\Customer\Model\Resource\Address\CollectionFactory;
use Magento\Customer\Model\Resource\Customer as ResourceCustomer;
use Magento\Customer\Api\Data\CustomerDataBuilder;
use Magento\Customer\Model\Data\Customer as CustomerData;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Api\AttributeDataBuilder;

/**
 * Customer model
 *
 * @method int getWebsiteId() getWebsiteId()
 * @method Customer setWebsiteId(int)
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
 */
class Customer extends \Magento\Framework\Model\AbstractExtensibleModel
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

    /**
     * Codes of exceptions related to customer model
     */
    const EXCEPTION_EMAIL_NOT_CONFIRMED = 1;

    const EXCEPTION_INVALID_EMAIL_OR_PASSWORD = 2;

    const EXCEPTION_EMAIL_EXISTS = 3;

    const EXCEPTION_INVALID_RESET_PASSWORD_LINK_TOKEN = 4;

    const SUBSCRIBED_YES = 'yes';

    const SUBSCRIBED_NO = 'no';

    const ENTITY = 'customer';

    /**
     * Configuration path to expiration period of reset password link
     */
    const XML_PATH_CUSTOMER_RESET_PASSWORD_LINK_EXPIRATION_PERIOD = 'customer/password/reset_link_expiration_period';

    /**
     * Model event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'customer';

    /**
     * Name of the event object
     *
     * @var string
     */
    protected $_eventObject = 'customer';

    /**
     * List of errors
     *
     * @var array
     */
    protected $_errors = [];

    /**
     * Assoc array of customer attributes
     *
     * @var array
     */
    protected $_attributes;

    /**
     * Customer addresses collection
     *
     * @var \Magento\Customer\Model\Resource\Address\Collection
     */
    protected $_addressesCollection;

    /**
     * Is model deletable
     *
     * @var boolean
     */
    protected $_isDeleteable = true;

    /**
     * Is model readonly
     *
     * @var boolean
     */
    protected $_isReadonly = false;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_config;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var Share
     */
    protected $_configShare;

    /**
     * @var AddressFactory
     */
    protected $_addressFactory;

    /**
     * @var CollectionFactory
     */
    protected $_addressesFactory;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var AttributeFactory
     */
    protected $_attributeFactory;

    /**
     * @var GroupRepositoryInterface
     */
    protected $_groupRepository;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $_encryptor;

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $mathRandom;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @var CustomerDataBuilder
     */
    protected $customerDataBuilder;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\MetadataServiceInterface $metadataService
     * @param AttributeDataBuilder $customAttributeBuilder
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Eav\Model\Config $config
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Resource\Customer $resource
     * @param Config\Share $configShare
     * @param AddressFactory $addressFactory
     * @param Resource\Address\CollectionFactory $addressesFactory
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param GroupRepositoryInterface $groupRepository
     * @param AttributeFactory $attributeFactory
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param CustomerDataBuilder $customerDataBuilder
     * @param DataObjectProcessor $dataObjectProcessor
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\MetadataServiceInterface $metadataService,
        AttributeDataBuilder $customAttributeBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Eav\Model\Config $config,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Model\Resource\Customer $resource,
        \Magento\Customer\Model\Config\Share $configShare,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Customer\Model\Resource\Address\CollectionFactory $addressesFactory,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        GroupRepositoryInterface $groupRepository,
        AttributeFactory $attributeFactory,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        CustomerDataBuilder $customerDataBuilder,
        DataObjectProcessor $dataObjectProcessor,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = []
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_config = $config;
        $this->_configShare = $configShare;
        $this->_addressFactory = $addressFactory;
        $this->_addressesFactory = $addressesFactory;
        $this->_transportBuilder = $transportBuilder;
        $this->_groupRepository = $groupRepository;
        $this->_attributeFactory = $attributeFactory;
        $this->_encryptor = $encryptor;
        $this->dateTime = $dateTime;
        $this->customerDataBuilder = $customerDataBuilder;
        $this->dataObjectProcessor = $dataObjectProcessor;
        parent::__construct(
            $context,
            $registry,
            $metadataService,
            $customAttributeBuilder,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Initialize customer model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('Magento\Customer\Model\Resource\Customer');
    }

    /**
     * Retrieve customer model with customer data
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    public function getDataModel()
    {
        $customerData = $this->getData();
        $addressesData = [];
        /** @var \Magento\Customer\Model\Address $address */
        foreach ($this->getAddresses() as $address) {
            $addressesData[] = $address->getDataModel();
        }
        return $this->customerDataBuilder
            ->populateWithArray($customerData)
            ->setAddresses($addressesData)
            ->setId($this->getId())
            ->create();
    }

    /**
     * Update customer data
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return $this
     */
    public function updateData($customer)
    {
        $customerDataAttributes = $this->dataObjectProcessor->buildOutputDataArray(
            $customer,
            '\Magento\Customer\Api\Data\CustomerInterface'
        );

        foreach ($customerDataAttributes as $attributeCode => $attributeData) {
            if ($attributeCode == 'password') {
                continue;
            }
            $this->setDataUsingMethod($attributeCode, $attributeData);
        }

        $customAttributes = $customer->getCustomAttributes();
        if (!is_null($customAttributes)) {
            foreach ($customAttributes as $attribute) {
                $this->setDataUsingMethod($attribute->getAttributeCode(), $attribute->getValue());
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
     * @throws \Magento\Framework\Model\Exception
     * @deprecated Use \Magento\Customer\Api\AccountManagementInterface::authenticate
     */
    public function authenticate($login, $password)
    {
        $this->loadByEmail($login);
        if ($this->getConfirmation() && $this->isConfirmationRequired()) {
            throw new \Magento\Framework\Model\Exception(
                __('This account is not confirmed.'),
                self::EXCEPTION_EMAIL_NOT_CONFIRMED
            );
        }
        if (!$this->validatePassword($password)) {
            throw new \Magento\Framework\Model\Exception(
                __('Invalid login or password.'),
                self::EXCEPTION_INVALID_EMAIL_OR_PASSWORD
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
     */
    public function loadByEmail($customerEmail)
    {
        $this->_getResource()->loadByEmail($this, $customerEmail);
        return $this;
    }

    /**
     * Processing object before save data
     *
     * @return $this
     */
    public function beforeSave()
    {
        parent::beforeSave();

        $storeId = $this->getStoreId();
        if ($storeId === null) {
            $this->setStoreId($this->_storeManager->getStore()->getId());
        }

        $this->getGroupId();
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function afterSave()
    {
        $customerData = (array)$this->getData();
        $customerData[CustomerData::ID] = $this->getId();
        $dataObject = $this->customerDataBuilder->populateWithArray($customerData)->create();
        $customerOrigData = (array)$this->getOrigData();
        $customerOrigData[CustomerData::ID] = $this->getId();
        $origDataObject = $this->customerDataBuilder->populateWithArray($customerOrigData)->create();
        $this->_eventManager->dispatch(
            'customer_save_after_data_object',
            ['customer_data_object' => $dataObject, 'orig_customer_data_object' => $origDataObject]
        );
        return parent::afterSave();
    }

    /**
     * Change customer password
     *
     * @param   string $newPassword
     * @return  $this
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
     */
    public function getAddressItemById($addressId)
    {
        return $this->getAddressesCollection()->getItemById($addressId);
    }

    /**
     * Retrieve not loaded address collection
     *
     * @return \Magento\Customer\Model\Resource\Address\Collection
     */
    public function getAddressCollection()
    {
        return $this->_createAddressCollection();
    }

    /**
     * Customer addresses collection
     *
     * @return \Magento\Customer\Model\Resource\Address\Collection
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
     * @return \Magento\Framework\Object[]
     */
    public function getAddresses()
    {
        return $this->getAddressesCollection()->getItems();
    }

    /**
     * Retrieve all customer attributes
     *
     * @return Attribute[]
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
     * @return  \Magento\Customer\Model\Resource\Attribute | null
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
     */
    public function getPrimaryBillingAddress()
    {
        return $this->getPrimaryAddress('default_billing');
    }

    /**
     * Get customer default billing address
     *
     * @return Address
     */
    public function getDefaultBillingAddress()
    {
        return $this->getPrimaryBillingAddress();
    }

    /**
     * Get default customer shipping address
     *
     * @return Address
     */
    public function getPrimaryShippingAddress()
    {
        return $this->getPrimaryAddress('default_shipping');
    }

    /**
     * Get default customer shipping address
     *
     * @return Address
     */
    public function getDefaultShippingAddress()
    {
        return $this->getPrimaryShippingAddress();
    }

    /**
     * Retrieve ids of default addresses
     *
     * @return array
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
            if ($primaryBilling->getId() == $primaryShipping->getId()) {
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
     * @throws \Magento\Framework\Model\Exception
     */
    public function sendNewAccountEmail($type = 'registered', $backUrl = '', $storeId = '0')
    {
        $types = $this->getTemplateTypes();

        if (!isset($types[$type])) {
            throw new \Magento\Framework\Model\Exception(__('Wrong transactional account email type'));
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
     * @deprecated
     */
    public function isConfirmationRequired()
    {
        if ($this->canSkipConfirmation()) {
            return false;
        }
        $storeId = $this->getStoreId() ? $this->getStoreId() : null;

        return (bool)$this->_scopeConfig->getValue(
            self::XML_PATH_IS_CONFIRM,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Generate random confirmation key
     *
     * @return string
     */
    public function getRandomConfirmationKey()
    {
        return md5(uniqid());
    }

    /**
     * Send email with new customer password
     *
     * @return $this
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
     */
    protected function _sendEmailTemplate($template, $sender, $templateParams = [], $storeId = null)
    {
        /** @var \Magento\Framework\Mail\TransportInterface $transport */
        $transport = $this->_transportBuilder->setTemplateIdentifier(
            $this->_scopeConfig->getValue($template, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId)
        )->setTemplateOptions(
            ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId]
        )->setTemplateVars(
            $templateParams
        )->setFrom(
            $this->_scopeConfig->getValue($sender, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId)
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
     */
    public function getGroupId()
    {
        if (!$this->hasData('group_id')) {
            $storeId = $this->getStoreId() ? $this->getStoreId() : $this->_storeManager->getStore()->getId();
            $groupId = $this->_scopeConfig->getValue(
                GroupManagement::XML_PATH_DEFAULT_ID,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
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
     */
    public function getStore()
    {
        return $this->_storeManager->getStore($this->getStoreId());
    }

    /**
     * Retrieve shared store ids
     *
     * @return array
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
     */
    public function setStore(\Magento\Store\Model\Store $store)
    {
        $this->setStoreId($store->getId());
        $this->setWebsiteId($store->getWebsite()->getId());
        return $this;
    }

    /**
     * Validate customer attribute values.
     * For existing customer password + confirmation will be validated only when password is set
     * (i.e. its change is requested)
     *
     * @return bool|string[]
     */
    public function validate()
    {
        $errors = [];
        if (!\Zend_Validate::is(trim($this->getFirstname()), 'NotEmpty')) {
            $errors[] = __('The first name cannot be empty.');
        }

        if (!\Zend_Validate::is(trim($this->getLastname()), 'NotEmpty')) {
            $errors[] = __('The last name cannot be empty.');
        }

        if (!\Zend_Validate::is($this->getEmail(), 'EmailAddress')) {
            $errors[] = __('Please correct this email address: "%1".', $this->getEmail());
        }

        $entityType = $this->_config->getEntityType('customer');
        $attribute = $this->_createCustomerAttribute();
        $attribute->loadByCode($entityType, 'dob');
        if ($attribute->getIsRequired() && '' == trim($this->getDob())) {
            $errors[] = __('The Date of Birth is required.');
        }
        $attribute = $this->_createCustomerAttribute();
        $attribute->loadByCode($entityType, 'taxvat');
        if ($attribute->getIsRequired() && '' == trim($this->getTaxvat())) {
            $errors[] = __('The TAX/VAT number is required.');
        }
        $attribute = $this->_createCustomerAttribute();
        $attribute->loadByCode($entityType, 'gender');
        if ($attribute->getIsRequired() && '' == trim($this->getGender())) {
            $errors[] = __('Gender is required.');
        }

        if (empty($errors)) {
            return true;
        }
        return $errors;
    }

    /**
     * Unset subscription
     *
     * @return $this
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
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * Reset errors array
     *
     * @return $this
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
     */
    public function beforeDelete()
    {
        //TODO : Revisit and figure handling permissions in MAGETWO-11084 Implementation: Service Context Provider
        return parent::beforeDelete();
    }

    /**
     * Get customer created at date timestamp
     *
     * @return int|null
     */
    public function getCreatedAtTimestamp()
    {
        $date = $this->getCreatedAt();
        if ($date) {
            return $this->dateTime->toTimestamp($date);
        }
        return null;
    }

    /**
     * Reset all model data
     *
     * @return $this
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
     */
    public function getEntityType()
    {
        return $this->_getResource()->getEntityType();
    }

    /**
     * Return Entity Type ID
     *
     * @return int
     */
    public function getEntityTypeId()
    {
        $entityTypeId = $this->getData('entity_type_id');
        if (!$entityTypeId) {
            $entityTypeId = $this->getEntityType()->getId();
            $this->setData('entity_type_id', $entityTypeId);
        }
        return $entityTypeId;
    }

    /**
     * Get either first store ID from a set website or the provided as default
     *
     * @param int|string|null $defaultStoreId
     *
     * @return int
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
     * @throws \Magento\Framework\Model\Exception
     */
    public function changeResetPasswordLinkToken($passwordLinkToken)
    {
        if (!is_string($passwordLinkToken) || empty($passwordLinkToken)) {
            throw new \Magento\Framework\Model\Exception(
                __('Invalid password reset token.'),
                self::EXCEPTION_INVALID_RESET_PASSWORD_LINK_TOKEN
            );
        }
        $this->_getResource()->changeResetPasswordLinkToken($this, $passwordLinkToken);
        return $this;
    }

    /**
     * Check if current reset password link token is expired
     *
     * @return boolean
     * @deprecated
     */
    public function isResetPasswordLinkTokenExpired()
    {
        $linkToken = $this->getRpToken();
        $linkTokenCreatedAt = $this->getRpTokenCreatedAt();

        if (empty($linkToken) || empty($linkTokenCreatedAt)) {
            return true;
        }

        $expirationPeriod = $this->getResetPasswordLinkExpirationPeriod();

        $currentTimestamp = $this->dateTime->toTimestamp($this->dateTime->now());
        $tokenTimestamp = $this->dateTime->toTimestamp($linkTokenCreatedAt);
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
     */
    public function getResetPasswordLinkExpirationPeriod()
    {
        return (int)$this->_scopeConfig->getValue(
            self::XML_PATH_CUSTOMER_RESET_PASSWORD_LINK_EXPIRATION_PERIOD,
            \Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT
        );
    }

    /**
     * @return Address
     */
    protected function _createAddressInstance()
    {
        return $this->_addressFactory->create();
    }

    /**
     * @return \Magento\Customer\Model\Resource\Address\Collection
     */
    protected function _createAddressCollection()
    {
        return $this->_addressesFactory->create();
    }

    /**
     * @return \Magento\Customer\Model\Attribute
     */
    protected function _createCustomerAttribute()
    {
        return $this->_attributeFactory->create();
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
}
