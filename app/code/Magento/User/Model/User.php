<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Model;

use Magento\Backend\Model\Auth\Credential\StorageInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\User\Api\Data\UserInterface;
use Magento\User\Model\Spi\NotificationExceptionInterface;
use Magento\User\Model\Spi\NotificatorInterface;
use Magento\Framework\App\DeploymentConfig;

/**
 * Admin user model
 *
 * @api
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
 * @api
 * @since 100.0.2
 */
class User extends AbstractModel implements StorageInterface, UserInterface
{
    /**
     * @deprecated Notificator is now responsible for sending messages to admin users.
     * @see \Magento\User\Model\Spi\NotificatorInterface
     */
    const XML_PATH_FORGOT_EMAIL_TEMPLATE = 'admin/emails/forgot_email_template';

    /**
     * @deprecated Notificator is now responsible for sending messages to admin users.
     * @see \Magento\User\Model\Spi\NotificatorInterface
     */
    const XML_PATH_FORGOT_EMAIL_IDENTITY = 'admin/emails/forgot_email_identity';

    /**
     * @deprecated Notificator is now responsible for sending messages to admin users.
     * @see \Magento\User\Model\Spi\NotificatorInterface
     */
    const XML_PATH_USER_NOTIFICATION_TEMPLATE = 'admin/emails/user_notification_template';

    /**
     * Configuration paths for admin user reset password email template
     *
     * @deprecated Notificator is now responsible for sending messages to admin users.
     * @see \Magento\User\Model\Spi\NotificatorInterface
     */
    const XML_PATH_RESET_PASSWORD_TEMPLATE = 'admin/emails/reset_password_template';

    const MESSAGE_ID_PASSWORD_EXPIRED = 'magento_user_password_expired';

    /**
     * Tag to use for user assigned role caching.
     */
    private const CACHE_TAG = 'user_assigned_role';

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
     * @deprecated 101.1.0
     */
    protected $_transportBuilder;

    /**
     * @deprecated 101.1.0
     */
    protected $_storeManager;

    /**
     * @var UserValidationRules
     */
    protected $validationRules;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var NotificatorInterface
     */
    private $notificator;

    /**
     * @deprecated 101.1.0
     */
    private $deploymentConfig;

    /**
     * @var array
     */
    protected $_cacheTag = [
        \Magento\Backend\Block\Menu::CACHE_TAGS,
        self::CACHE_TAG,
    ];

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
     * @param UserValidationRules $validationRules
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @param Json $serializer
     * @param DeploymentConfig|null $deploymentConfig
     * @param NotificatorInterface|null $notificator
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
        array $data = [],
        Json $serializer = null,
        DeploymentConfig $deploymentConfig = null,
        ?NotificatorInterface $notificator = null
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
        $this->serializer = $serializer
            ?: ObjectManager::getInstance()->get(Json::class);
        $this->deploymentConfig = $deploymentConfig
            ?: ObjectManager::getInstance()->get(DeploymentConfig::class);
        $this->notificator = $notificator
            ?: ObjectManager::getInstance()->get(NotificatorInterface::class);
    }

    /**
     * Initialize user model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\User\Model\ResourceModel\User::class);
    }

    /**
     * Removing dependencies and leaving only entity's properties.
     *
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
                '_validatorBeforeSave',
                'validationRules',
                'serializer',
                'deploymentConfig',
                'notificator'
            ]
        );
    }

    /**
     * Restoring required objects after serialization.
     *
     * @return void
     */
    public function __wakeup()
    {
        parent::__wakeup();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->serializer = $objectManager->get(Json::class);
        $this->_eventManager = $objectManager->get(\Magento\Framework\Event\ManagerInterface::class);
        $this->_userData = $objectManager->get(\Magento\User\Helper\Data::class);
        $this->_config = $objectManager->get(\Magento\Backend\App\ConfigInterface::class);
        $this->_registry = $objectManager->get(\Magento\Framework\Registry::class);
        $this->_validatorObject = $objectManager->get(\Magento\Framework\Validator\DataObjectFactory::class);
        $this->_roleFactory = $objectManager->get(\Magento\Authorization\Model\RoleFactory::class);
        $this->_encryptor = $objectManager->get(\Magento\Framework\Encryption\EncryptorInterface::class);
        $this->_transportBuilder = $objectManager->get(\Magento\Framework\Mail\Template\TransportBuilder::class);
        $this->_storeManager = $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
        $this->validationRules = $objectManager->get(UserValidationRules::class);
        $this->deploymentConfig = $objectManager->get(DeploymentConfig::class);
        $this->notificator = $objectManager->get(NotificatorInterface::class);
    }

    /**
     * Processing data before model save
     *
     * @return $this
     */
    public function beforeSave()
    {
        $data = [
            'extra' => $this->serializer->serialize($this->getExtra()),
        ];

        if ($this->_willSavePassword()) {
            $data['password'] = $this->_getEncodedPassword($this->getPassword());
        }

        if ($this->getIsActive() !== null) {
            $data['is_active'] = (int)$this->getIsActive();
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
     * @throws \Exception
     */
    public function validate()
    {
        $validator = $this->_getValidationRulesBeforeSave();

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
     * @throws \Exception
     * @since 100.0.3
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
            $data = $this->serializer->serialize($data);
        }
        $this->_getResource()->saveExtra($this, $data);
        return $this;
    }

    /**
     * Retrieve user roles
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
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
     * Check if such combination role/user exists.
     *
     * @return bool
     */
    public function roleUserExists()
    {
        $result = $this->_getResource()->roleUserExists($this);
        return is_array($result) && count($result) > 0 ? true : false;
    }

    /**
     * Send email with reset password confirmation link.
     *
     * @return $this
     * @throws NotificationExceptionInterface
     * @deprecated 101.1.0
     * @see NotificatorInterface::sendForgotPassword()
     */
    public function sendPasswordResetConfirmationEmail()
    {
        $this->notificator->sendForgotPassword($this);

        return $this;
    }

    /**
     * Send email to when password is resetting
     *
     * @throws NotificationExceptionInterface
     * @return $this
     * @deprecated 100.1.0
     */
    public function sendPasswordResetNotificationEmail()
    {
        $this->sendNotificationEmailsIfRequired();
        return $this;
    }

    /**
     * Check changes and send notification emails.
     *
     * @throws NotificationExceptionInterface
     * @return $this
     * @since 100.1.0
     */
    public function sendNotificationEmailsIfRequired()
    {
        if ($this->isObjectNew()) {
            //Notification about a new user.
            $this->notificator->sendCreated($this);
        } elseif ($changes = $this->createChangesDescriptionString()) {
            //User changed.
            $this->notificator->sendUpdated($this, explode(', ', $changes));
        }

        return $this;
    }

    /**
     * Create changes description string
     *
     * @return string
     * @since 100.1.0
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

        if ($this->getUserName() != $this->getOrigData('username') && $this->getOrigData('username')) {
            $changes[] = __('username');
        }

        return implode(', ', $changes);
    }

    /**
     * Send user notification email.
     *
     * @param string $changes
     * @param string $email
     * @throws NotificationExceptionInterface
     * @return $this
     * @since 100.1.0
     * @deprecated 101.1.0
     * @see NotificatorInterface::sendUpdated()
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function sendUserNotificationEmail($changes, $email = null)
    {
        $this->notificator->sendUpdated($this, explode(', ', $changes));

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
        return $this->getFirstName() . $separator . $this->getLastName();
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
            $sensitive = $config ? $username == $this->getUserName() : true;
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
                    __(
                        'The account sign-in was incorrect or your account is disabled temporarily. '
                        . 'Please wait and try again later.'
                    )
                );
            }
            if (!$this->hasAssigned2Role($this->getId())) {
                throw new AuthenticationException(__('More permissions are needed to access this.'));
            }
            $result = true;
        }
        return $result;
    }

    /**
     * Login user
     *
     * @param string $username
     * @param string $password
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
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
            if (is_string($data['extra'])) {
                $data['extra'] = $this->serializer->unserialize($data['extra']);
            }

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
        if ($user instanceof AbstractModel) {
            $userId = $user->getUserId();
        } elseif (is_numeric($user) && (int)$user !== 0) {
            $userId = $user;
        } else {
            return null;
        }
        $data = $this->_cacheManager->load('assigned_role_' . $userId);
        if (false === $data) {
            $data = $this->getResource()->hasAssigned2Role($user);

            $this->_cacheManager->save(
                $this->serializer->serialize($data),
                'assigned_role_' . $userId,
                [self::CACHE_TAG]
            );
        } else {
            $data = $this->serializer->unserialize($data);
        }

        return $data;
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
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The password reset token is incorrect. Verify the token and try again.')
            );
        }
        $this->setRpToken($newToken);
        $this->setRpTokenCreatedAt((new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT));

        return $this;
    }

    /**
     * Check if current reset password link token is expired
     *
     * @return bool
     * @throws \Exception
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
     * @inheritDoc
     */
    public function getFirstName()
    {
        return $this->_getData('firstname');
    }

    /**
     * @inheritDoc
     */
    public function setFirstName($firstName)
    {
        return $this->setData('firstname', $firstName);
    }

    /**
     * @inheritDoc
     */
    public function getLastName()
    {
        return $this->_getData('lastname');
    }

    /**
     * @inheritDoc
     */
    public function setLastName($lastName)
    {
        return $this->setData('lastname', $lastName);
    }

    /**
     * @inheritDoc
     */
    public function getEmail()
    {
        return $this->_getData('email');
    }

    /**
     * @inheritDoc
     */
    public function setEmail($email)
    {
        return $this->setData('email', $email);
    }

    /**
     * @inheritDoc
     */
    public function getUserName()
    {
        return $this->_getData('username');
    }

    /**
     * @inheritDoc
     */
    public function setUserName($userName)
    {
        return $this->setData('username', $userName);
    }

    /**
     * @inheritDoc
     */
    public function getPassword()
    {
        return $this->_getData('password');
    }

    /**
     * @inheritDoc
     */
    public function setPassword($password)
    {
        return $this->setData('password', $password);
    }

    /**
     * @inheritDoc
     */
    public function getCreated()
    {
        return $this->_getData('created');
    }

    /**
     * @inheritDoc
     */
    public function setCreated($created)
    {
        return $this->setData('created', $created);
    }

    /**
     * @inheritDoc
     */
    public function getModified()
    {
        return $this->_getData('modified');
    }

    /**
     * @inheritDoc
     */
    public function setModified($modified)
    {
        return $this->setData('modified', $modified);
    }

    /**
     * @inheritDoc
     */
    public function getIsActive()
    {
        return $this->_getData('is_active');
    }

    /**
     * @inheritDoc
     */
    public function setIsActive($isActive)
    {
        return $this->setData('is_active', $isActive);
    }

    /**
     * @inheritDoc
     */
    public function getInterfaceLocale()
    {
        return $this->_getData('interface_locale');
    }

    /**
     * @inheritDoc
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
     * @since 100.1.0
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
                __('Your account is temporarily disabled. Please try again later.')
            );
        }

        if (!$isCheckSuccessful) {
            throw new \Magento\Framework\Exception\AuthenticationException(
                __('The password entered for the current user is invalid. Verify the password and try again.')
            );
        }

        return $this;
    }
}
