<?php
/**
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
 * @category    Magento
 * @package     Magento_Customer
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Customer\Model;

use Magento\Customer\Service\V1\Dto\CustomerBuilder as CustomerDtoBuilder;

/**
 * Customer model
 *
 * @method int getWebsiteId() getWebsiteId()
 * @method \Magento\Customer\Model\Customer setWebsiteId(int)
 * @method int getStoreId() getStoreId()
 * @method string getEmail() getEmail()
 * @method \Magento\Customer\Model\Resource\Customer _getResource()
 * @method mixed getDisableAutoGroupChange()
 * @method \Magento\Customer\Model\Customer setDisableAutoGroupChange($value)
 * @method \Magento\Customer\Model\Customer setGroupId($value)
 * @method \Magento\Customer\Model\Customer setDefaultBilling($value)
 * @method \Magento\Customer\Model\Customer setDefaultShipping($value)
 */
class Customer extends \Magento\Core\Model\AbstractModel
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

    const XML_PATH_IS_CONFIRM                   = 'customer/create_account/confirm';
    const XML_PATH_CONFIRM_EMAIL_TEMPLATE       = 'customer/create_account/email_confirmation_template';
    const XML_PATH_CONFIRMED_EMAIL_TEMPLATE     = 'customer/create_account/email_confirmed_template';
    const XML_PATH_GENERATE_HUMAN_FRIENDLY_ID   = 'customer/create_account/generate_human_friendly_id';

    /**
     * Codes of exceptions related to customer model
     */
    const EXCEPTION_EMAIL_NOT_CONFIRMED       = 1;
    const EXCEPTION_INVALID_EMAIL_OR_PASSWORD = 2;
    const EXCEPTION_EMAIL_EXISTS              = 3;
    const EXCEPTION_INVALID_RESET_PASSWORD_LINK_TOKEN = 4;

    const SUBSCRIBED_YES = 'yes';
    const SUBSCRIBED_NO  = 'no';

    const ENTITY = 'customer';

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
    protected $_errors = array();

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
     * Is model deleteable
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

    /** @var \Magento\Core\Model\StoreManagerInterface */
    protected $_storeManager;

    /** @var \Magento\Eav\Model\Config */
    protected $_config;

    /**
     * Customer data
     *
     * @var \Magento\Customer\Helper\Data
     */
    protected $_customerData = null;

    /**
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_coreStoreConfig;

    /**
     * @var \Magento\Customer\Model\Config\Share
     */
    protected $_configShare;

    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    protected $_addressFactory;

    /**
     * @var \Magento\Customer\Model\Resource\Address\CollectionFactory
     */
    protected $_addressesFactory;

    /**
     * @var \Magento\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Magento\Customer\Model\AttributeFactory
     */
    protected $_attributeFactory;

    /**
     * @var \Magento\Customer\Service\V1\CustomerGroupServiceInterface
     */
    protected $_groupService;

    /**
     * @var \Magento\Encryption\EncryptorInterface
     */
    protected $_encryptor;

    /**
     * @var \Magento\Math\Random
     */
    protected $mathRandom;

    /**
     * @var \Magento\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @var CustomerDtoBuilder
     */
    protected $_customerDtoBuilder;

    /**
     * @param \Magento\Model\Context $context
     * @param \Magento\Registry $registry
     * @param \Magento\Customer\Helper\Data $customerData
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Eav\Model\Config $config
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param Resource\Customer $resource
     * @param Config\Share $configShare
     * @param AddressFactory $addressFactory
     * @param Resource\Address\CollectionFactory $addressesFactory
     * @param \Magento\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Customer\Service\V1\CustomerGroupServiceInterface $groupService
     * @param AttributeFactory $attributeFactory
     * @param \Magento\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Math\Random $mathRandom
     * @param \Magento\Stdlib\DateTime $dateTime
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param CustomerDtoBuilder $customerDtoBuilder
     * @param array $data
     */
    public function __construct(
        \Magento\Model\Context $context,
        \Magento\Registry $registry,
        \Magento\Customer\Helper\Data $customerData,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Eav\Model\Config $config,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Customer\Model\Resource\Customer $resource,
        \Magento\Customer\Model\Config\Share $configShare,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Customer\Model\Resource\Address\CollectionFactory $addressesFactory,
        \Magento\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Customer\Service\V1\CustomerGroupServiceInterface $groupService,
        \Magento\Customer\Model\AttributeFactory $attributeFactory,
        \Magento\Encryption\EncryptorInterface $encryptor,
        \Magento\Math\Random $mathRandom,
        \Magento\Stdlib\DateTime $dateTime,
        \Magento\Data\Collection\Db $resourceCollection = null,
        CustomerDtoBuilder $customerDtoBuilder,
        array $data = array()
    ) {
        $this->_customerData = $customerData;
        $this->_coreStoreConfig = $coreStoreConfig;
        $this->_storeManager = $storeManager;
        $this->_config = $config;
        $this->_configShare = $configShare;
        $this->_addressFactory = $addressFactory;
        $this->_addressesFactory = $addressesFactory;
        $this->_transportBuilder = $transportBuilder;
        $this->_groupService = $groupService;
        $this->_attributeFactory = $attributeFactory;
        $this->_encryptor = $encryptor;
        $this->mathRandom = $mathRandom;
        $this->dateTime = $dateTime;
        $this->_customerDtoBuilder = $customerDtoBuilder;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize customer model
     */
    public function _construct()
    {
        $this->_init('Magento\Customer\Model\Resource\Customer');
    }

    /**
     * Retrieve customer sharing configuration model
     *
     * @return \Magento\Customer\Model\Config\Share
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
     * @throws \Magento\Core\Exception
     * @return boolean
     *
     */
    public function authenticate($login, $password)
    {
        $this->loadByEmail($login);
        if ($this->getConfirmation() && $this->isConfirmationRequired()) {
            throw new \Magento\Core\Exception(__('This account is not confirmed.'), self::EXCEPTION_EMAIL_NOT_CONFIRMED);
        }
        if (!$this->validatePassword($password)) {
            throw new \Magento\Core\Exception(
                __('Invalid login or password.'),
                self::EXCEPTION_INVALID_EMAIL_OR_PASSWORD
            );
        }
        $this->_eventManager->dispatch('customer_customer_authenticated', array(
                'model'    => $this,
                'password' => $password,
            ));

        return true;
    }

    /**
     * Load customer by email
     *
     * @param   string $customerEmail
     * @return  \Magento\Customer\Model\Customer
     */
    public function loadByEmail($customerEmail)
    {
        $this->_getResource()->loadByEmail($this, $customerEmail);
        return $this;
    }


    /**
     * Processing object before save data
     *
     * @return \Magento\Customer\Model\Customer
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();

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
    protected function _afterSave()
    {
        $customerData = (array)$this->getData();
        $customerData[\Magento\Customer\Service\V1\Dto\Customer::ID] = $this->getId();
        $dataDto = $this->_customerDtoBuilder->populateWithArray($customerData)->create();
        $customerOrigData = (array)$this->getOrigData();
        $customerOrigData[\Magento\Customer\Service\V1\Dto\Customer::ID] = $this->getId();
        $origDataDto = $this->_customerDtoBuilder->populateWithArray($customerOrigData)->create();
        $this->_eventManager->dispatch(
            'customer_save_after_dto',
            array('customer_dto' => $dataDto, 'orig_customer_dto' => $origDataDto)
        );
        return parent::_afterSave();
    }

    /**
     * Change customer password
     *
     * @param   string $newPassword
     * @return  \Magento\Customer\Model\Customer
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
        $name .=  ' ' . $this->getLastname();
        if ($this->_config->getAttribute('customer', 'suffix')->getIsVisible() && $this->getSuffix()) {
            $name .= ' ' . $this->getSuffix();
        }
        return $name;
    }

    /**
     * Add address to address collection
     *
     * @param   \Magento\Customer\Model\Address $address
     * @return  \Magento\Customer\Model\Customer
     */
    public function addAddress(\Magento\Customer\Model\Address $address)
    {
        $this->getAddressesCollection()->addItem($address);
        return $this;
    }

    /**
     * Retrieve customer address by address id
     *
     * @param   int $addressId
     * @return  \Magento\Customer\Model\Address
     */
    public function getAddressById($addressId)
    {
        return $this->_createAddressInstance()->load($addressId);
    }

    /**
     * Getting customer address object from collection by identifier
     *
     * @param int $addressId
     * @return \Magento\Customer\Model\Address
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
            $this->_addressesCollection = $this->getAddressCollection()
                ->setCustomerFilter($this)
                ->addAttributeToSelect('*');
            foreach ($this->_addressesCollection as $address) {
                $address->setCustomer($this);
            }
        }

        return $this->_addressesCollection;
    }

    /**
     * Retrieve customer address array
     *
     * @return array
     */
    public function getAddresses()
    {
        return $this->getAddressesCollection()->getItems();
    }

    /**
     * Retrieve all customer attributes
     *
     * @return \Magento\Customer\Model\Attribute[]
     */
    public function getAttributes()
    {
        if ($this->_attributes === null) {
            $this->_attributes = $this->_getResource()
                ->loadAllAttributes($this)
                ->getSortedAttributes();
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
     * @return \Magento\Customer\Model\Customer
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
     * @param   string $password
     * @param   int    $salt
     * @return  string
     */
    public function hashPassword($password, $salt = null)
    {
        return $this->_encryptor->getHash($password, !is_null($salt) ? $salt : 2);
    }

    /**
     * Retrieve random password
     *
     * @param   int $length
     * @return  string
     */
    public function generatePassword($length = 6)
    {
        return $this->mathRandom->getRandomString($length);
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
     * @return  \Magento\Customer\Model\Address
     */
    public function getPrimaryAddress($attributeCode)
    {
        $primaryAddress = $this->getAddressesCollection()->getItemById($this->getData($attributeCode));

        return $primaryAddress ? $primaryAddress : false;
    }

    /**
     * Get customer default billing address
     *
     * @return \Magento\Customer\Model\Address
     */
    public function getPrimaryBillingAddress()
    {
        return $this->getPrimaryAddress('default_billing');
    }

    /**
     * Get customer default billing address
     *
     * @return \Magento\Customer\Model\Address
     */
    public function getDefaultBillingAddress()
    {
        return $this->getPrimaryBillingAddress();
    }

    /**
     * Get default customer shipping address
     *
     * @return \Magento\Customer\Model\Address
     */
    public function getPrimaryShippingAddress()
    {
        return $this->getPrimaryAddress('default_shipping');
    }

    /**
     * Get default customer shipping address
     *
     * @return \Magento\Customer\Model\Address
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
        $ids = array();
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
     * @return array
     */
    public function getPrimaryAddresses()
    {
        $addresses = array();
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
     * @return array
     */
    public function getAdditionalAddresses()
    {
        $addresses = array();
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
     * @param \Magento\Customer\Model\Address $address
     * @return boolean
     */
    public function isAddressPrimary(\Magento\Customer\Model\Address $address)
    {
        if (!$address->getId()) {
            return false;
        }
        return ($address->getId() == $this->getDefaultBilling()) || ($address->getId() == $this->getDefaultShipping());
    }

    /**
     * Send email with new account related information
     *
     * @param string $type
     * @param string $backUrl
     * @param string $storeId
     * @throws \Magento\Core\Exception
     * @return \Magento\Customer\Model\Customer
     */
    public function sendNewAccountEmail($type = 'registered', $backUrl = '', $storeId = '0')
    {
        $types = array(
            'registered'     => self::XML_PATH_REGISTER_EMAIL_TEMPLATE,  // welcome email, when confirmation is disabled
            'confirmed'      => self::XML_PATH_CONFIRMED_EMAIL_TEMPLATE, // welcome email, when confirmation is enabled
            'confirmation'   => self::XML_PATH_CONFIRM_EMAIL_TEMPLATE,   // email with confirmation link
        );
        if (!isset($types[$type])) {
            throw new \Magento\Core\Exception(__('Wrong transactional account email type'));
        }

        if (!$storeId) {
            $storeId = $this->_getWebsiteStoreId($this->getSendemailStoreId());
        }

        $this->_sendEmailTemplate($types[$type], self::XML_PATH_REGISTER_EMAIL_IDENTITY,
            array('customer' => $this, 'back_url' => $backUrl, 'store' => $this->getStore()), $storeId);

        return $this;
    }

    /**
     * Check if accounts confirmation is required in config
     *
     * @return bool
     */
    public function isConfirmationRequired()
    {
        if ($this->canSkipConfirmation()) {
            return false;
        }
        $storeId = $this->getStoreId() ? $this->getStoreId() : null;

        return (bool)$this->_coreStoreConfig->getConfig(self::XML_PATH_IS_CONFIRM, $storeId);
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
     * @return \Magento\Customer\Model\Customer
     */
    public function sendPasswordReminderEmail()
    {
        $this->_sendEmailTemplate(self::XML_PATH_REMIND_EMAIL_TEMPLATE, self::XML_PATH_FORGOT_EMAIL_IDENTITY,
            array('customer' => $this, 'store' => $this->getStore()), $this->getStoreId());

        return $this;
    }

    /**
     * Send corresponding email template
     *
     * @param string $template configuration path of email template
     * @param string $sender configuration path of email identity
     * @param array $templateParams
     * @param int|null $storeId
     * @return \Magento\Customer\Model\Customer
     */
    protected function _sendEmailTemplate($template, $sender, $templateParams = array(), $storeId = null)
    {
        /** @var \Magento\Mail\TransportInterface $transport */
        $transport =  $this->_transportBuilder
            ->setTemplateIdentifier($this->_coreStoreConfig->getConfig($template, $storeId))
            ->setTemplateOptions(array(
                'area' => \Magento\Core\Model\App\Area::AREA_FRONTEND,
                'store' => $storeId
            ))
            ->setTemplateVars($templateParams)
            ->setFrom($this->_coreStoreConfig->getConfig($sender, $storeId))
            ->addTo($this->getEmail(), $this->getName())
            ->getTransport();
        $transport->sendMessage();

        return $this;
    }

    /**
     * Send email with reset password confirmation link
     *
     * @return \Magento\Customer\Model\Customer
     */
    public function sendPasswordResetConfirmationEmail()
    {
        $storeId = $this->getStoreId();
        if (!$storeId) {
            $storeId = $this->_getWebsiteStoreId();
        }

        $this->_sendEmailTemplate(self::XML_PATH_FORGOT_EMAIL_TEMPLATE, self::XML_PATH_FORGOT_EMAIL_IDENTITY,
            array('customer' => $this, 'store' => $this->getStore()), $storeId
        );

        return $this;
    }

    /**
     * Send email to when password is resetting
     *
     * @return \Magento\Customer\Model\Customer
     */
    public function sendPasswordResetNotificationEmail()
    {
        $storeId = $this->getStoreId();
        if (!$storeId) {
            $storeId = $this->_getWebsiteStoreId();
        }

        /** @var \Magento\Mail\TransportInterface $transport */
        $transport =  $this->_transportBuilder
            ->setTemplateIdentifier(
                $this->_coreStoreConfig->getConfig(self::XML_PATH_RESET_PASSWORD_TEMPLATE, $storeId)
            )
            ->setTemplateOptions(array(
                'area' => \Magento\Core\Model\App\Area::AREA_FRONTEND,
                'store' => $storeId
            ))
            ->setTemplateVars(array('customer' => $this, 'store' => $this->getStore()))
            ->setFrom($this->_coreStoreConfig->getConfig(self::XML_PATH_FORGOT_EMAIL_IDENTITY, $storeId))
            ->addTo($this->getEmail(), $this->getName())
            ->getTransport();
        $transport->sendMessage();

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
            $groupId = $this->_groupService->getDefaultGroup($storeId)->getId();
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
            $groupTaxClassId = $this->_groupService->getGroup($this->getGroupId())->getTaxClassId();
            $this->setData('tax_class_id', $groupTaxClassId);
        }
        return $this->getData('tax_class_id');
    }

    /**
     * Retrieve store where customer was created
     *
     * @return \Magento\Core\Model\Store
     */
    public function getStore()
    {
        return $this->_storeManager->getStore($this->getStoreId());
    }

    /**
     * Retrieve shared store ids
     *
     * @deprecated Use \Magento\Customer\Helper\Data::getSharedStoreIds
     * @return array
     */
    public function getSharedStoreIds()
    {
        $ids = $this->_getData('shared_store_ids');
        if ($ids === null) {
            $ids = array();
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
     * @return array
     */
    public function getSharedWebsiteIds()
    {
        $ids = $this->_getData('shared_website_ids');
        if ($ids === null) {
            $ids = array();
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
     * @param \Magento\Core\Model\Store $store
     * @return \Magento\Customer\Model\Customer
     */
    public function setStore(\Magento\Core\Model\Store $store)
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
     * @return bool|array
     */
    public function validate()
    {
        $errors = array();
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
     * @return \Magento\Customer\Model\Customer
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
     */
    public function cleanAllAddresses()
    {
        $this->_addressesCollection = null;
    }

    /**
     * Add error
     *
     * @param $error
     * @return \Magento\Customer\Model\Customer
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
     * @return \Magento\Customer\Model\Customer
     */
    public function resetErrors()
    {
        $this->_errors = array();
        return $this;
    }

    /**
     * Prepare customer for delete
     */
    protected function _beforeDelete()
    {
        //TODO : Revisit and figure handling permissions in MAGETWO-11084 Implementation: Service Context Provider
        //$this->_protectFromNonAdmin();
        return parent::_beforeDelete();
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
     * @return \Magento\Customer\Model\Customer
     */
    public function reset()
    {
        $this->setData(array());
        $this->setOrigData();
        $this->_attributes = null;

        return $this;
    }

    /**
     * Checks model is deleteable
     *
     * @return boolean
     */
    public function isDeleteable()
    {
        return $this->_isDeleteable;
    }

    /**
     * Set is deleteable flag
     *
     * @param boolean $value
     * @return \Magento\Customer\Model\Customer
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
     * @return \Magento\Customer\Model\Customer
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
    public function canSkipConfirmation()
    {
        return $this->getId() && $this->hasSkipConfirmationIfEmail()
        && strtolower($this->getSkipConfirmationIfEmail()) === strtolower($this->getEmail());
    }

    /**
     * Clone current object
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
     * @throws \Magento\Core\Exception
     * @param string $passwordLinkToken
     * @return \Magento\Customer\Model\Customer
     */
    public function changeResetPasswordLinkToken($passwordLinkToken)
    {
        if (!is_string($passwordLinkToken) || empty($passwordLinkToken)) {
            throw new \Magento\Core\Exception(
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
     */
    public function isResetPasswordLinkTokenExpired()
    {
        $linkToken = $this->getRpToken();
        $linkTokenCreatedAt = $this->getRpTokenCreatedAt();

        if (empty($linkToken) || empty($linkTokenCreatedAt)) {
            return true;
        }

        $expirationPeriod = $this->_customerData->getResetPasswordLinkExpirationPeriod();

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
     * @return \Magento\Customer\Model\Address
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
}
