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
 * @category    Mage
 * @package     Mage_User
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Admin user model
 *
 * @method Mage_User_Model_Resource_User _getResource()
 * @method Mage_User_Model_Resource_User getResource()
 * @method string getFirstname()
 * @method Mage_User_Model_User setFirstname(string $value)
 * @method string getLastname()
 * @method Mage_User_Model_User setLastname(string $value)
 * @method string getEmail()
 * @method Mage_User_Model_User setEmail(string $value)
 * @method string getUsername()
 * @method Mage_User_Model_User setUsername(string $value)
 * @method string getPassword()
 * @method Mage_User_Model_User setPassword(string $value)
 * @method string getCreated()
 * @method Mage_User_Model_User setCreated(string $value)
 * @method string getModified()
 * @method Mage_User_Model_User setModified(string $value)
 * @method string getLogdate()
 * @method Mage_User_Model_User setLogdate(string $value)
 * @method int getLognum()
 * @method Mage_User_Model_User setLognum(int $value)
 * @method int getReloadAclFlag()
 * @method Mage_User_Model_User setReloadAclFlag(int $value)
 * @method int getIsActive()
 * @method Mage_User_Model_User setIsActive(int $value)
 * @method string getExtra()
 * @method Mage_User_Model_User setExtra(string $value)
 *
 * @category    Mage
 * @package     Mage_User
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_User_Model_User
    extends Mage_Core_Model_Abstract
    implements Mage_Backend_Model_Auth_Credential_StorageInterface
{
    /**
     * Configuration paths for email templates and identities
     */
    const XML_PATH_FORGOT_EMAIL_TEMPLATE    = 'admin/emails/forgot_email_template';
    const XML_PATH_FORGOT_EMAIL_IDENTITY    = 'admin/emails/forgot_email_identity';

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
     * @var Mage_User_Model_Role
     */
    protected $_role;

    /**
     * Available resources flag
     *
     * @var boolean
     */
    protected $_hasResources = true;

    /**
     * Mail handler
     *
     * @var  Mage_Core_Model_Email_Template_Mailer
     */
    protected $_mailer;

    /**
     * Initialize user model
     */
    protected function _construct()
    {
        $this->_init('Mage_User_Model_Resource_User');
    }

    /**
     * Processing data before model save
     *
     * @return Mage_User_Model_User
     */
    protected function _beforeSave()
    {
        $data = array(
            'firstname' => $this->getFirstname(),
            'lastname'  => $this->getLastname(),
            'email'     => $this->getEmail(),
            'modified'  => now(),
            'extra'     => serialize($this->getExtra())
        );

        if ($this->getId() > 0) {
            $data['user_id'] = $this->getId();
        }

        if ( $this->getUsername() ) {
            $data['username'] = $this->getUsername();
        }

        if ($this->getNewPassword()) {
            // Change password
            $data['password'] = $this->_getEncodedPassword($this->getNewPassword());
        } elseif ($this->getPassword() && $this->getPassword() != $this->getOrigData('password')) {
            // New user password
            $data['password'] = $this->_getEncodedPassword($this->getPassword());
        }

        if ($this->getIsActive() !== null) {
            $data['is_active'] = intval($this->getIsActive());
        }

        $this->addData($data);

        return parent::_beforeSave();
    }

    /**
     * Process data after model is saved
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _afterSave()
    {
        $this->_role = null;
        return parent::_afterSave();
    }

    /**
     * Save admin user extra data (like configuration sections state)
     *
     * @param   array $data
     * @return  Mage_User_Model_User
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
     * @return Mage_User_Model_Role
     */
    public function getRole()
    {
        if (null === $this->_role) {
            $this->_role = Mage::getModel('Mage_User_Model_Role');
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
     * @return Mage_User_Model_User
     */
    public function deleteFromRole()
    {
        $this->_getResource()->deleteFromRole($this);
        return $this;
    }

    /**
     * Check if such combination role/user exists
     *
     * @return boolean
     */
    public function roleUserExists()
    {
        $result = $this->_getResource()->roleUserExists($this);
        return (is_array($result) && count($result) > 0) ? true : false;
    }

    /**
     * Check if user exists based on its id, username and email
     *
     * @return boolean
     */
    public function userExists()
    {
        $result = $this->_getResource()->userExists($this);
        return (is_array($result) && count($result) > 0) ? true : false;
    }

    /**
     * Retrieve admin user collection
     *
     * @return Mage_User_Model_Resource_User_Collection
     */
    public function getCollection()
    {
        return Mage::getResourceModel('Mage_User_Model_Resource_User_Collection');
    }

    /**
     * Set custom mail handler
     *
     * @param Mage_Core_Model_Email_Template_Mailer $mailer
     * @return Mage_User_Model_User
     */
    public function setMailer(Mage_Core_Model_Email_Template_Mailer $mailer)
    {
        $this->_mailer = $mailer;
        return $this;
    }

    /**
     * Retrieve mailer
     *
     * @return Mage_Core_Model_Email_Template_Mailer
     */
    protected function _getMailer()
    {
        if (!$this->_mailer) {
            $this->_mailer = Mage::getModel('Mage_Core_Model_Email_Template_Mailer');
        }
        return $this->_mailer;
    }

    /**
     * Send email with reset password confirmation link
     *
     * @return Mage_User_Model_User
     */
    public function sendPasswordResetConfirmationEmail()
    {
        $mailer = $this->_getMailer();
        /** @var $mailer Mage_Core_Model_Email_Template_Mailer */
        $emailInfo = Mage::getModel('Mage_Core_Model_Email_Info');
        $emailInfo->addTo($this->getEmail(), $this->getName());
        $mailer->addEmailInfo($emailInfo);

        // Set all required params and send emails
        $mailer->setSender(Mage::getStoreConfig(self::XML_PATH_FORGOT_EMAIL_IDENTITY));
        $mailer->setStoreId(0);
        $mailer->setTemplateId(Mage::getStoreConfig(self::XML_PATH_FORGOT_EMAIL_TEMPLATE));
        $mailer->setTemplateParams(array(
            'user' => $this
        ));
        $mailer->send();

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
        return 'U' . $this->getUserId();
    }

    /**
     * Authenticate user name and password and save loaded record
     *
     * @param string $username
     * @param string $password
     * @return boolean
     * @throws Mage_Core_Exception
     * @throws Mage_Backend_Model_Auth_Exception
     * @throws Mage_Backend_Model_Auth_Plugin_Exception
     */
    public function authenticate($username, $password)
    {
        $config = Mage::getStoreConfigFlag('admin/security/use_case_sensitive_login');
        $result = false;

        try {
            Mage::dispatchEvent('admin_user_authenticate_before', array(
                'username' => $username,
                'user'     => $this
            ));
            $this->loadByUsername($username);
            $sensitive = ($config) ? $username == $this->getUsername() : true;

            if ($sensitive
                && $this->getId()
                && Mage::helper('Mage_Core_Helper_Data')->validateHash($password, $this->getPassword())
            ) {
                if ($this->getIsActive() != '1') {
                    throw new Mage_Backend_Model_Auth_Exception(
                        Mage::helper('Mage_User_Helper_Data')->__('This account is inactive.')
                    );
                }
                if (!$this->hasAssigned2Role($this->getId())) {
                    throw new Mage_Backend_Model_Auth_Exception(
                        Mage::helper('Mage_User_Helper_Data')->__('Access denied.')
                    );
                }
                $result = true;
            }

            Mage::dispatchEvent('admin_user_authenticate_after', array(
                'username' => $username,
                'password' => $password,
                'user'     => $this,
                'result'   => $result,
            ));
        } catch (Mage_Core_Exception $e) {
            $this->unsetData();
            throw $e;
        }

        if (!$result) {
            $this->unsetData();
        }
        return $result;
    }

    /**
     * Login user
     *
     * @param   string $username
     * @param   string $password
     * @return  Mage_User_Model_User
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
     * @return Mage_User_Model_User
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
     * @return Mage_User_Model_User
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
     * @param int|Mage_User_Model_User $user
     * @return null|boolean|array
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
        return Mage::helper('Mage_Core_Helper_Data')->getHash($password, 2);
    }

    /**
     * Validate user attribute values.
     * Returns TRUE or array of errors.
     *
     * @return mixed
     */
    public function validate()
    {
        $errors = array();

        if (!Zend_Validate::is($this->getUsername(), 'NotEmpty')) {
            $errors[] = Mage::helper('Mage_User_Helper_Data')->__('User Name is required field.');
        }

        if (!Zend_Validate::is($this->getFirstname(), 'NotEmpty')) {
            $errors[] = Mage::helper('Mage_User_Helper_Data')->__('First Name is required field.');
        }

        if (!Zend_Validate::is($this->getLastname(), 'NotEmpty')) {
            $errors[] = Mage::helper('Mage_User_Helper_Data')->__('Last Name is required field.');
        }

        if (!Zend_Validate::is($this->getEmail(), 'EmailAddress')) {
            $errors[] = Mage::helper('Mage_User_Helper_Data')->__('Please enter a valid email.');
        }

        if ($this->hasNewPassword()) {
            $errors = array_merge($errors, $this->_validatePassword());
        }

        if ($this->userExists()) {
            // @codingStandardsIgnoreStart
            $errors[] = Mage::helper('Mage_User_Helper_Data')->__('A user with the same user name or email aleady exists.');
            // @codingStandardsIgnoreEnd
        }

        if (empty($errors)) {
            return true;
        }
        return $errors;
    }

    /**
     * Validate user password
     *
     * @return array
     */
    protected function _validatePassword()
    {
        $errors = array();

        if (Mage::helper('Mage_Core_Helper_String')->strlen($this->getNewPassword()) < self::MIN_PASSWORD_LENGTH) {
            // @codingStandardsIgnoreStart
            $errors[] = Mage::helper('Mage_User_Helper_Data')->__('Password must be at least of %d characters.', self::MIN_PASSWORD_LENGTH);
            // @codingStandardsIgnoreEnd
        }

        if (!preg_match('/[a-z]/iu', $this->getNewPassword())
            || !preg_match('/[0-9]/u', $this->getNewPassword())
        ) {
            // @codingStandardsIgnoreStart
            $errors[] = Mage::helper('Mage_User_Helper_Data')->__('Password must include both numeric and alphabetic characters.');
            // @codingStandardsIgnoreEnd
        }

        if ($this->hasPasswordConfirmation() && $this->getNewPassword() != $this->getPasswordConfirmation()) {
            // @codingStandardsIgnoreStart
            $errors[] = Mage::helper('Mage_User_Helper_Data')->__('Password confirmation must be same as password.');
            // @codingStandardsIgnoreEnd
        }

        return $errors;
    }

    /**
     * Change reset password link token
     *
     * Stores new reset password link token and its creation time
     *
     * @param string $newToken
     * @return Mage_User_Model_User
     * @throws Mage_Core_Exception
     */
    public function changeResetPasswordLinkToken($newToken)
    {
        if (!is_string($newToken) || empty($newToken)) {
            Mage::throwException(
                'Mage_Core',
                Mage::helper('Mage_User_Helper_Data')->__('Invalid password reset token.')
            );
        }
        $this->setRpToken($newToken);
        $currentDate = Varien_Date::now();
        $this->setRpTokenCreatedAt($currentDate);

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

        $expirationPeriod = Mage::helper('Mage_User_Helper_Data')->getResetPasswordLinkExpirationPeriod();

        $currentDate = Varien_Date::now();
        $currentTimestamp = Varien_Date::toTimestamp($currentDate);
        $tokenTimestamp = Varien_Date::toTimestamp($linkTokenCreatedAt);
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
     * @return Mage_User_Model_User
     */
    public function setHasAvailableResources($hasResources)
    {
        $this->_hasResources = $hasResources;
        return $this;
    }
}
