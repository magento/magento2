<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Model;

use Magento\Backend\Model\Auth\Credential\StorageInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Admin user model
 *
 * @method \Magento\User\Model\Resource\User _getResource()
 * @method \Magento\User\Model\Resource\User getResource()
 * @method string getFirstname()
 * @method \Magento\User\Model\User setFirstname(string $value)
 * @method string getLastname()
 * @method \Magento\User\Model\User setLastname(string $value)
 * @method string getEmail()
 * @method string getUsername()
 * @method \Magento\User\Model\User setUsername(string $value)
 * @method string getPassword()
 * @method \Magento\User\Model\User setPassword(string $value)
 * @method string getCreated()
 * @method \Magento\User\Model\User setCreated(string $value)
 * @method string getModified()
 * @method \Magento\User\Model\User setModified(string $value)
 * @method string getLogdate()
 * @method \Magento\User\Model\User setLogdate(string $value)
 * @method int getLognum()
 * @method \Magento\User\Model\User setLognum(int $value)
 * @method int getReloadAclFlag()
 * @method \Magento\User\Model\User setReloadAclFlag(int $value)
 * @method int getIsActive()
 * @method \Magento\User\Model\User setIsActive(int $value)
 * @method string getExtra()
 * @method \Magento\User\Model\User setExtra(string $value)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class User extends AbstractModel implements StorageInterface
{
    /**
     * Configuration paths for email templates and identities
     */
    const XML_PATH_FORGOT_EMAIL_TEMPLATE = 'admin/emails/forgot_email_template';

    const XML_PATH_FORGOT_EMAIL_IDENTITY = 'admin/emails/forgot_email_identity';

    const XML_PATH_RESET_PASSWORD_TEMPLATE = 'admin/emails/reset_password_template';

    /**
     * Minimum length of admin password
     */
    const MIN_PASSWORD_LENGTH = 7;

    /**
     * Model event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'admin_user';

    /**
     * Admin role
     *
     * @var \Magento\Authorization\Model\Role
     */
    protected $_role;

    /**
     * Available resources flag
     *
     * @var bool
     */
    protected $_hasResources = true;

    /**
     * User data
     *
     * @var \Magento\User\Helper\Data
     */
    protected $_userData = null;

    /**
     * Core store config
     *
     * @var \Magento\Backend\App\ConfigInterface
     */
    protected $_config;

    /**
     * Factory for validator composite object
     *
     * @var \Magento\Framework\Validator\ObjectFactory
     */
    protected $_validatorObject;

    /**
     * Role model factory
     *
     * @var \Magento\Authorization\Model\RoleFactory
     */
    protected $_roleFactory;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $_encryptor;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\User\Helper\Data $userData
     * @param \Magento\Backend\App\ConfigInterface $config
     * @param \Magento\Framework\Validator\ObjectFactory $validatorObjectFactory
     * @param \Magento\Authorization\Model\RoleFactory $roleFactory
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\User\Helper\Data $userData,
        \Magento\Backend\App\ConfigInterface $config,
        \Magento\Framework\Validator\ObjectFactory $validatorObjectFactory,
        \Magento\Authorization\Model\RoleFactory $roleFactory,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = []
    ) {
        $this->_encryptor = $encryptor;
        $this->dateTime = $dateTime;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_userData = $userData;
        $this->_config = $config;
        $this->_validatorObject = $validatorObjectFactory;
        $this->_roleFactory = $roleFactory;
        $this->_transportBuilder = $transportBuilder;
        $this->_storeManager = $storeManager;
    }

    /**
     * Initialize user model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\User\Model\Resource\User');
    }

    /**
     * @return string[]
     */
    public function __sleep()
    {
        $properties = parent::__sleep();
        return array_diff(
            $properties,
            [
                '_eventManager',
                '_userData',
                '_config',
                '_validatorObject',
                '_roleFactory',
                '_encryptor',
                '_transportBuilder',
                '_storeManager',
                '_validatorBeforeSave'
            ]
        );
    }

    /**
     * @return void
     */
    public function __wakeup()
    {
        parent::__wakeup();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_eventManager = $objectManager->get('Magento\Framework\Event\ManagerInterface');
        $this->_userData = $objectManager->get('Magento\User\Helper\Data');
        $this->_config = $objectManager->get('Magento\Backend\App\ConfigInterface');
        $this->_registry = $objectManager->get('Magento\Framework\Registry');
        $this->_validatorObject = $objectManager->get('Magento\Framework\Validator\ObjectFactory');
        $this->_roleFactory = $objectManager->get('Magento\Authorization\Model\RoleFactory');
        $this->_encryptor = $objectManager->get('Magento\Framework\Encryption\EncryptorInterface');
        $this->_transportBuilder = $objectManager->get('Magento\Framework\Mail\Template\TransportBuilder');
        $this->_storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
    }

    /**
     * Processing data before model save
     *
     * @return $this
     */
    public function beforeSave()
    {
        $data = [
            'firstname' => $this->getFirstname(),
            'lastname' => $this->getLastname(),
            'email' => $this->getEmail(),
            'modified' => $this->dateTime->now(),
            'extra' => serialize($this->getExtra()),
        ];

        if ($this->getId() > 0) {
            $data['user_id'] = $this->getId();
        }

        if ($this->getUsername()) {
            $data['username'] = $this->getUsername();
        }

        if ($this->_willSavePassword()) {
            $data['password'] = $this->_getEncodedPassword($this->getPassword());
        }

        if (!is_null($this->getIsActive())) {
            $data['is_active'] = intval($this->getIsActive());
        }

        $this->addData($data);

        return parent::beforeSave();
    }

    /**
     * Whether the password saving is going to occur
     *
     * @return bool
     */
    protected function _willSavePassword()
    {
        return $this->isObjectNew() || $this->hasData('password') && $this->dataHasChangedFor('password');
    }

    /**
     * Add validation rules for particular fields
     *
     * @return \Zend_Validate_Interface
     */
    protected function _getValidationRulesBeforeSave()
    {
        $userNameNotEmpty = new \Zend_Validate_NotEmpty();
        $userNameNotEmpty->setMessage(__('User Name is a required field.'), \Zend_Validate_NotEmpty::IS_EMPTY);
        $firstNameNotEmpty = new \Zend_Validate_NotEmpty();
        $firstNameNotEmpty->setMessage(__('First Name is a required field.'), \Zend_Validate_NotEmpty::IS_EMPTY);
        $lastNameNotEmpty = new \Zend_Validate_NotEmpty();
        $lastNameNotEmpty->setMessage(__('Last Name is a required field.'), \Zend_Validate_NotEmpty::IS_EMPTY);
        $emailValidity = new \Zend_Validate_EmailAddress();
        $emailValidity->setMessage(__('Please enter a valid email.'), \Zend_Validate_EmailAddress::INVALID);

        /** @var $validator \Magento\Framework\Validator\Object */
        $validator = $this->_validatorObject->create();
        $validator->addRule(
            $userNameNotEmpty,
            'username'
        )->addRule(
            $firstNameNotEmpty,
            'firstname'
        )->addRule(
            $lastNameNotEmpty,
            'lastname'
        )->addRule(
            $emailValidity,
            'email'
        );

        if ($this->_willSavePassword()) {
            $this->_addPasswordValidation($validator);
        }
        return $validator;
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
        if (!\Zend_Validate::is(trim($this->getUsername()), 'NotEmpty')) {
            $errors[] = __('The user name cannot be empty.');
        }

        if (!\Zend_Validate::is(trim($this->getFirstname()), 'NotEmpty')) {
            $errors[] = __('The first name cannot be empty.');
        }

        if (!\Zend_Validate::is(trim($this->getLastname()), 'NotEmpty')) {
            $errors[] = __('The last name cannot be empty.');
        }

        if (!\Zend_Validate::is($this->getEmail(), 'EmailAddress')) {
            $errors[] = __('Please correct this email address: "%1".', $this->getEmail());
        }

        if (empty($errors)) {
            return true;
        }
        return $errors;
    }

    /**
     * Add validation rules for the password management fields
     *
     * @param \Magento\Framework\Validator\Object $validator
     * @return void
     */
    protected function _addPasswordValidation(\Magento\Framework\Validator\Object $validator)
    {
        $passwordNotEmpty = new \Zend_Validate_NotEmpty();
        $passwordNotEmpty->setMessage(__('Password is required field.'), \Zend_Validate_NotEmpty::IS_EMPTY);
        $minPassLength = self::MIN_PASSWORD_LENGTH;
        $passwordLength = new \Zend_Validate_StringLength(['min' => $minPassLength, 'encoding' => 'UTF-8']);
        $passwordLength->setMessage(
            __('Your password must be at least %1 characters.', $minPassLength),
            \Zend_Validate_StringLength::TOO_SHORT
        );
        $passwordChars = new \Zend_Validate_Regex('/[a-z].*\d|\d.*[a-z]/iu');
        $passwordChars->setMessage(
            __('Your password must include both numeric and alphabetic characters.'),
            \Zend_Validate_Regex::NOT_MATCH
        );
        $validator->addRule(
            $passwordNotEmpty,
            'password'
        )->addRule(
            $passwordLength,
            'password'
        )->addRule(
            $passwordChars,
            'password'
        );
        if ($this->hasPasswordConfirmation()) {
            $passwordConfirmation = new \Zend_Validate_Identical($this->getPasswordConfirmation());
            $passwordConfirmation->setMessage(
                __('Your password confirmation must match your password.'),
                \Zend_Validate_Identical::NOT_SAME
            );
            $validator->addRule($passwordConfirmation, 'password');
        }
    }

    /**
     * Process data after model is saved
     *
     * @return $this
     */
    public function afterSave()
    {
        $this->_role = null;
        return parent::afterSave();
    }

    /**
     * Save admin user extra data (like configuration sections state)
     *
     * @param   array $data
     * @return  $this
     */
    public function saveExtra($data)
    {
        if (is_array($data)) {
            $data = serialize($data);
        }
        $this->_getResource()->saveExtra($this, $data);
        return $this;
    }

    /**
     * Retrieve user roles
     *
     * @return array
     */
    public function getRoles()
    {
        return $this->_getResource()->getRoles($this);
    }

    /**
     * Get admin role model
     *
     * @return \Magento\Authorization\Model\Role
     */
    public function getRole()
    {
        if (null === $this->_role) {
            $this->_role = $this->_roleFactory->create();
            $roles = $this->getRoles();
            if ($roles && isset($roles[0]) && $roles[0]) {
                $this->_role->load($roles[0]);
            }
        }
        return $this->_role;
    }

    /**
     * Unassign user from his current role
     *
     * @return $this
     */
    public function deleteFromRole()
    {
        $this->_getResource()->deleteFromRole($this);
        return $this;
    }

    /**
     * Check if such combination role/user exists
     *
     * @return bool
     */
    public function roleUserExists()
    {
        $result = $this->_getResource()->roleUserExists($this);
        return is_array($result) && count($result) > 0 ? true : false;
    }

    /**
     * Send email with reset password confirmation link
     *
     * @return $this
     */
    public function sendPasswordResetConfirmationEmail()
    {
        // Set all required params and send emails
        /** @var \Magento\Framework\Mail\TransportInterface $transport */
        $transport = $this->_transportBuilder->setTemplateIdentifier(
            $this->_config->getValue(self::XML_PATH_FORGOT_EMAIL_TEMPLATE)
        )->setTemplateOptions(
            ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => 0]
        )->setTemplateVars(
            ['user' => $this, 'store' => $this->_storeManager->getStore(0)]
        )->setFrom(
            $this->_config->getValue(self::XML_PATH_FORGOT_EMAIL_IDENTITY)
        )->addTo(
            $this->getEmail(),
            $this->getName()
        )->getTransport();

        $transport->sendMessage();
        return $this;
    }

    /**
     * Send email to when password is resetting
     *
     * @return $this
     */
    public function sendPasswordResetNotificationEmail()
    {
        // Set all required params and send emails
        /** @var \Magento\Framework\Mail\TransportInterface $transport */
        $transport = $this->_transportBuilder->setTemplateIdentifier(
            $this->_config->getValue(self::XML_PATH_RESET_PASSWORD_TEMPLATE)
        )->setTemplateOptions(
            ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => 0]
        )->setTemplateVars(
            ['user' => $this, 'store' => $this->_storeManager->getStore(0)]
        )->setFrom(
            $this->_config->getValue(self::XML_PATH_FORGOT_EMAIL_IDENTITY)
        )->addTo(
            $this->getEmail(),
            $this->getName()
        )->getTransport();

        $transport->sendMessage();
        return $this;
    }

    /**
     * Retrieve user name
     *
     * @param string $separator
     * @return string
     */
    public function getName($separator = ' ')
    {
        return $this->getFirstname() . $separator . $this->getLastname();
    }

    /**
     * Retrieve user identifier
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->getUserId();
    }

    /**
     * Get user ACL role
     *
     * @return string
     */
    public function getAclRole()
    {
        return $this->getRole()->getId();
    }

    /**
     * Authenticate user name and password and save loaded record
     *
     * @param string $username
     * @param string $password
     * @return bool
     * @throws \Magento\Framework\Model\Exception
     * @throws \Magento\Backend\Model\Auth\Exception
     * @throws \Magento\Backend\Model\Auth\Plugin\Exception
     */
    public function authenticate($username, $password)
    {
        $config = $this->_config->isSetFlag('admin/security/use_case_sensitive_login');
        $result = false;

        try {
            $this->_eventManager->dispatch(
                'admin_user_authenticate_before',
                ['username' => $username, 'user' => $this]
            );
            $this->loadByUsername($username);
            $sensitive = $config ? $username == $this->getUsername() : true;
            if ($sensitive && $this->getId()) {
                $result = $this->verifyIdentity($password);
            }

            $this->_eventManager->dispatch(
                'admin_user_authenticate_after',
                ['username' => $username, 'password' => $password, 'user' => $this, 'result' => $result]
            );
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->unsetData();
            throw $e;
        }

        if (!$result) {
            $this->unsetData();
        }
        return $result;
    }

    /**
     * Ensure that provided password matches the current user password. Check if the current user account is active.
     *
     * @param string $password
     * @return bool
     * @throws \Magento\Backend\Model\Auth\Exception
     */
    public function verifyIdentity($password)
    {
        $result = false;
        if ($this->_encryptor->validateHash($password, $this->getPassword())) {
            if ($this->getIsActive() != '1') {
                throw new \Magento\Backend\Model\Auth\Exception(__('This account is inactive.'));
            }
            if (!$this->hasAssigned2Role($this->getId())) {
                throw new \Magento\Backend\Model\Auth\Exception(__('Access denied.'));
            }
            $result = true;
        }
        return $result;
    }

    /**
     * Login user
     *
     * @param   string $username
     * @param   string $password
     * @return  $this
     */
    public function login($username, $password)
    {
        if ($this->authenticate($username, $password)) {
            $this->getResource()->recordLogin($this);
        }
        return $this;
    }

    /**
     * Reload current user
     *
     * @return $this
     */
    public function reload()
    {
        $userId = $this->getId();
        $this->setId(null);
        $this->load($userId);
        return $this;
    }

    /**
     * Load user by its username
     *
     * @param string $username
     * @return $this
     */
    public function loadByUsername($username)
    {
        $data = $this->getResource()->loadByUsername($username);
        if ($data !== false) {
            $this->setData($data);
        }
        return $this;
    }

    /**
     * Check if user is assigned to any role
     *
     * @param int|\Magento\User\Model\User $user
     * @return null|array
     */
    public function hasAssigned2Role($user)
    {
        return $this->getResource()->hasAssigned2Role($user);
    }

    /**
     * Retrieve encoded password
     *
     * @param string $password
     * @return string
     */
    protected function _getEncodedPassword($password)
    {
        return $this->_encryptor->getHash($password, true);
    }

    /**
     * Change reset password link token
     *
     * Stores new reset password link token and its creation time
     *
     * @param string $newToken
     * @return $this
     * @throws \Magento\Framework\Model\Exception
     */
    public function changeResetPasswordLinkToken($newToken)
    {
        if (!is_string($newToken) || empty($newToken)) {
            throw new \Magento\Framework\Model\Exception(__('Please correct the password reset token.'));
        }
        $this->setRpToken($newToken);
        $this->setRpTokenCreatedAt($this->dateTime->now());

        return $this;
    }

    /**
     * Check if current reset password link token is expired
     *
     * @return bool
     */
    public function isResetPasswordLinkTokenExpired()
    {
        $linkToken = $this->getRpToken();
        $linkTokenCreatedAt = $this->getRpTokenCreatedAt();

        if (empty($linkToken) || empty($linkTokenCreatedAt)) {
            return true;
        }

        $expirationPeriod = $this->_userData->getResetPasswordLinkExpirationPeriod();

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
     * Check if user has available resources
     *
     * @return bool
     */
    public function hasAvailableResources()
    {
        return $this->_hasResources;
    }

    /**
     * Set user has available resources
     *
     * @param bool $hasResources
     * @return $this
     */
    public function setHasAvailableResources($hasResources)
    {
        $this->_hasResources = $hasResources;
        return $this;
    }
}
