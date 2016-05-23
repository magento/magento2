<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Model;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Backend\Model\Auth\Credential\StorageInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Store\Model\Store;
use Magento\User\Api\Data\UserInterface;

/**
 * Admin user model
 *
 * @method \Magento\User\Model\ResourceModel\User _getResource()
 * @method \Magento\User\Model\ResourceModel\User getResource()
 * @method string getLogdate()
 * @method \Magento\User\Model\User setLogdate(string $value)
 * @method int getLognum()
 * @method \Magento\User\Model\User setLognum(int $value)
 * @method int getReloadAclFlag()
 * @method \Magento\User\Model\User setReloadAclFlag(int $value)
 * @method string getExtra()
 * @method \Magento\User\Model\User setExtra(string $value)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class User extends AbstractModel implements StorageInterface, UserInterface
{
    /**
     * Configuration paths for email templates and identities
     */
    const XML_PATH_FORGOT_EMAIL_TEMPLATE = 'admin/emails/forgot_email_template';

    const XML_PATH_FORGOT_EMAIL_IDENTITY = 'admin/emails/forgot_email_identity';

    const XML_PATH_USER_NOTIFICATION_TEMPLATE = 'admin/emails/user_notification_template';

    /** @deprecated */
    const XML_PATH_RESET_PASSWORD_TEMPLATE = 'admin/emails/reset_password_template';

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
     * @var \Magento\Framework\Validator\DataObjectFactory
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
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var UserValidationRules
     */
    protected $validationRules;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\User\Helper\Data $userData
     * @param \Magento\Backend\App\ConfigInterface $config
     * @param \Magento\Framework\Validator\DataObjectFactory $validatorObjectFactory
     * @param \Magento\Authorization\Model\RoleFactory $roleFactory
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param UserValidationRules $validationRules
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\User\Helper\Data $userData,
        \Magento\Backend\App\ConfigInterface $config,
        \Magento\Framework\Validator\DataObjectFactory $validatorObjectFactory,
        \Magento\Authorization\Model\RoleFactory $roleFactory,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        UserValidationRules $validationRules,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_encryptor = $encryptor;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_userData = $userData;
        $this->_config = $config;
        $this->_validatorObject = $validatorObjectFactory;
        $this->_roleFactory = $roleFactory;
        $this->_transportBuilder = $transportBuilder;
        $this->_storeManager = $storeManager;
        $this->validationRules = $validationRules;
    }

    /**
     * Initialize user model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\User\Model\ResourceModel\User');
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
        $this->_validatorObject = $objectManager->get('Magento\Framework\Validator\DataObjectFactory');
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
            'extra' => serialize($this->getExtra()),
        ];

        if ($this->_willSavePassword()) {
            $data['password'] = $this->_getEncodedPassword($this->getPassword());
        }

        if ($this->getIsActive() !== null) {
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
        /** @var $validator \Magento\Framework\Validator\DataObject */
        $validator = $this->_validatorObject->create();
        $this->validationRules->addUserInfoRules($validator);

        // Add validation rules for the password management fields
        if ($this->_willSavePassword()) {
            $this->validationRules->addPasswordRules($validator);
            if ($this->hasPasswordConfirmation()) {
                $this->validationRules->addPasswordConfirmationRule($validator, $this->getPasswordConfirmation());
            }
        }
        return $validator;
    }

    /**
     * Validate admin user data.
     *
     * Existing user password confirmation will be validated only when password is set
     *
     * @return bool|string[]
     */
    public function validate()
    {
        /** @var $validator \Magento\Framework\Validator\DataObject */
        $validator = $this->_validatorObject->create();
        $this->validationRules->addUserInfoRules($validator);

        if (!$validator->isValid($this)) {
            return $validator->getMessages();
        }

        return $this->validatePasswordChange();
    }

    /**
     * Make sure admin password was changed.
     *
     * New password is compared to at least 4 previous passwords to prevent setting them again
     *
     * @return bool|string[]
     */
    protected function validatePasswordChange()
    {
        $password = $this->getPassword();
        if ($password && !$this->getForceNewPassword() && $this->getId()) {
            $errorMessage = __('Sorry, but this password has already been used. Please create another.');
            // Check if password is equal to the current one
            if ($this->_encryptor->isValidHash($password, $this->getOrigData('password'))) {
                return [$errorMessage];
            }

            // Check whether password was used before
            foreach ($this->getResource()->getOldPasswords($this) as $oldPasswordHash) {
                if ($this->_encryptor->isValidHash($password, $oldPasswordHash)) {
                    return [$errorMessage];
                }
            }
        }
        return true;
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
        $templateId = $this->_config->getValue(self::XML_PATH_FORGOT_EMAIL_TEMPLATE);
        $transport = $this->_transportBuilder->setTemplateIdentifier($templateId)
            ->setTemplateModel('Magento\Email\Model\BackendTemplate')
            ->setTemplateOptions(['area' => FrontNameResolver::AREA_CODE, 'store' => Store::DEFAULT_STORE_ID])
            ->setTemplateVars(['user' => $this, 'store' => $this->_storeManager->getStore(Store::DEFAULT_STORE_ID)])
            ->setFrom($this->_config->getValue(self::XML_PATH_FORGOT_EMAIL_IDENTITY))
            ->addTo($this->getEmail(), $this->getName())
            ->getTransport();

        $transport->sendMessage();
        return $this;
    }

    /**
     * Send email to when password is resetting
     *
     * @return $this
     * @deprecated
     */
    public function sendPasswordResetNotificationEmail()
    {
        $this->sendNotificationEmailsIfRequired();
        return $this;
    }

    /**
     * Check changes and send notification emails
     *
     * @return $this
     */
    public function sendNotificationEmailsIfRequired()
    {
        $changes = $this->createChangesDescriptionString();

        if ($changes) {
            if ($this->getEmail() != $this->getOrigData('email') && $this->getOrigData('email')) {
                $this->sendUserNotificationEmail($changes, $this->getOrigData('email'));
            }
            $this->sendUserNotificationEmail($changes);
        }

        return $this;
    }

    /**
     * Create changes description string
     *
     * @return string
     */
    protected function createChangesDescriptionString()
    {
        $changes = [];

        if ($this->getEmail() != $this->getOrigData('email') && $this->getOrigData('email')) {
            $changes[] = __('email');
        }

        if ($this->getPassword()
            && $this->getOrigData('password')
            && $this->getPassword() != $this->getOrigData('password')) {
            $changes[] = __('password');
        }

        if ($this->getUsername() != $this->getOrigData('username') && $this->getOrigData('username')) {
            $changes[] = __('username');
        }

        return implode(', ', $changes);
    }

    /**
     * Send user notification email
     *
     * @param string $changes
     * @param string $email
     * @return $this
     */
    protected function sendUserNotificationEmail($changes, $email = null)
    {
        if ($email === null) {
            $email = $this->getEmail();
        }

        $transport = $this->_transportBuilder
            ->setTemplateIdentifier($this->_config->getValue(self::XML_PATH_USER_NOTIFICATION_TEMPLATE))
            ->setTemplateModel('Magento\Email\Model\BackendTemplate')
            ->setTemplateOptions(['area' => FrontNameResolver::AREA_CODE, 'store' => Store::DEFAULT_STORE_ID])
            ->setTemplateVars(
                [
                    'user' => $this,
                    'store' => $this->_storeManager->getStore(Store::DEFAULT_STORE_ID),
                    'changes' => $changes
                ]
            )
            ->setFrom($this->_config->getValue(self::XML_PATH_FORGOT_EMAIL_IDENTITY))
            ->addTo($email, $this->getName())
            ->getTransport();

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
     * @throws \Magento\Framework\Exception\LocalizedException
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
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
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
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    public function verifyIdentity($password)
    {
        $result = false;
        if ($this->_encryptor->validateHash($password, $this->getPassword())) {
            if ($this->getIsActive() != '1') {
                throw new AuthenticationException(
                    __('You did not sign in correctly or your account is temporarily disabled.')
                );
            }
            if (!$this->hasAssigned2Role($this->getId())) {
                throw new AuthenticationException(__('You need more permissions to access this.'));
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
            $this->setOrigData();
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
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function changeResetPasswordLinkToken($newToken)
    {
        if (!is_string($newToken) || empty($newToken)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Please correct the password reset token.'));
        }
        $this->setRpToken($newToken);
        $this->setRpTokenCreatedAt((new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT));

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

        $currentTimestamp = (new \DateTime())->getTimestamp();
        $tokenTimestamp = (new \DateTime($linkTokenCreatedAt))->getTimestamp();
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

    /**
     * {@inheritdoc}
     */
    public function getFirstName()
    {
        return $this->_getData('firstname');
    }

    /**
     * {@inheritdoc}
     */
    public function setFirstName($firstName)
    {
        return $this->setData('firstname', $firstName);
    }

    /**
     * {@inheritdoc}
     */
    public function getLastName()
    {
        return $this->_getData('lastname');
    }

    /**
     * {@inheritdoc}
     */
    public function setLastName($lastName)
    {
        return $this->setData('lastname', $lastName);
    }

    /**
     * {@inheritdoc}
     */
    public function getEmail()
    {
        return $this->_getData('email');
    }

    /**
     * {@inheritdoc}
     */
    public function setEmail($email)
    {
        return $this->setData('email', $email);
    }

    /**
     * {@inheritdoc}
     */
    public function getUserName()
    {
        return $this->_getData('username');
    }

    /**
     * {@inheritdoc}
     */
    public function setUserName($userName)
    {
        return $this->setData('username', $userName);
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        return $this->_getData('password');
    }

    /**
     * {@inheritdoc}
     */
    public function setPassword($password)
    {
        return $this->setData('password', $password);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreated()
    {
        return $this->_getData('created');
    }

    /**
     * {@inheritdoc}
     */
    public function setCreated($created)
    {
        return $this->setData('created', $created);
    }

    /**
     * {@inheritdoc}
     */
    public function getModified()
    {
        return $this->_getData('modified');
    }

    /**
     * {@inheritdoc}
     */
    public function setModified($modified)
    {
        return $this->setData('modified', $modified);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsActive()
    {
        return $this->_getData('is_active');
    }

    /**
     * {@inheritdoc}
     */
    public function setIsActive($isActive)
    {
        return $this->setData('is_active', $isActive);
    }

    /**
     * {@inheritdoc}
     */
    public function getInterfaceLocale()
    {
        return $this->_getData('interface_locale');
    }

    /**
     * {@inheritdoc}
     */
    public function setInterfaceLocale($interfaceLocale)
    {
        return $this->setData('interface_locale', $interfaceLocale);
    }

    /**
     * Security check for admin user
     *
     * @param string $passwordString
     * @return $this
     * @throws \Magento\Framework\Exception\State\UserLockedException
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    public function performIdentityCheck($passwordString)
    {
        try {
            $isCheckSuccessful = $this->verifyIdentity($passwordString);
        } catch (\Magento\Framework\Exception\AuthenticationException $e) {
            $isCheckSuccessful = false;
        }
        $this->_eventManager->dispatch(
            'admin_user_authenticate_after',
            [
                'username' => $this->getUserName(),
                'password' => $passwordString,
                'user' => $this,
                'result' => $isCheckSuccessful
            ]
        );
        // Check if lock information has been updated in observers
        $clonedUser = clone($this);
        $clonedUser->reload();
        if ($clonedUser->getLockExpires()) {
            throw new \Magento\Framework\Exception\State\UserLockedException(
                __('Your account is temporarily disabled.')
            );
        }

        if (!$isCheckSuccessful) {
            throw new \Magento\Framework\Exception\AuthenticationException(
                __('You have entered an invalid password for current user.')
            );
        }

        return $this;
    }
}
