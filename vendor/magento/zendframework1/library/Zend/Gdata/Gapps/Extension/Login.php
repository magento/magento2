<?php

/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Gapps
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Gdata_Extension
 */
#require_once 'Zend/Gdata/Extension.php';

/**
 * @see Zend_Gdata_Gapps
 */
#require_once 'Zend/Gdata/Gapps.php';

/**
 * Represents the apps:login element used by the Apps data API. This
 * class is used to describe properties of a user, and is usually contained
 * within instances of Zene_Gdata_Gapps_UserEntry or any other class
 * which is linked to a particular username.
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Gapps
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Gapps_Extension_Login extends Zend_Gdata_Extension
{

    protected $_rootNamespace = 'apps';
    protected $_rootElement = 'login';

    /**
     * The username for this user. This is used as the user's email address
     * and when logging in to Google Apps-hosted services.
     *
     * @var string
     */
    protected $_username = null;

    /**
     * The password for the user. May be in cleartext or as an SHA-1
     * digest, depending on the value of _hashFunctionName.
     *
     * @var string
     */
    protected $_password = null;

    /**
     * Specifies whether the password stored in _password is in cleartext
     * or is an SHA-1 digest of a password. If the password is cleartext,
     * then this should be null. If the password is an SHA-1 digest, then
     * this should be set to 'SHA-1'.
     *
     * At the time of writing, no other hash functions are supported
     *
     * @var string
     */
    protected $_hashFunctionName = null;

    /**
     * True if the user has administrative rights for this domain, false
     * otherwise.
     *
     * @var boolean
     */
    protected $_admin = null;

    /**
     * True if the user has agreed to the terms of service for Google Apps,
     * false otherwise.
     *
     * @var boolean.
     */
    protected $_agreedToTerms = null;

    /**
     * True if this user has been suspended, false otherwise.
     *
     * @var boolean
     */
    protected $_suspended = null;

    /**
     * True if the user will be required to change their password at
     * their next login, false otherwise.
     *
     * @var boolean
     */
    protected $_changePasswordAtNextLogin = null;

    /**
     * Constructs a new Zend_Gdata_Gapps_Extension_Login object.
     *
     * @param string $username (optional) The username to be used for this
     *          login.
     * @param string $password (optional) The password to be used for this
     *          login.
     * @param string $hashFunctionName (optional) The name of the hash
     *          function used to protect the password, or null if no
     *          has function has been applied. As of this writing,
     *          the only valid values are 'SHA-1' or null.
     * @param boolean $admin (optional) Whether the user is an administrator
     *          or not.
     * @param boolean $suspended (optional) Whether this login is suspended or not.
     * @param boolean $changePasswordAtNextLogin (optional) Whether
     *          the user is required to change their password at their
     *          next login.
     * @param boolean $agreedToTerms (optional) Whether the user has
     *          agreed to the terms of service.
     */
    public function __construct($username = null, $password = null,
        $hashFunctionName = null, $admin = null, $suspended = null,
        $changePasswordAtNextLogin = null, $agreedToTerms = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Gapps::$namespaces);
        parent::__construct();
        $this->_username = $username;
        $this->_password = $password;
        $this->_hashFunctionName = $hashFunctionName;
        $this->_admin = $admin;
        $this->_agreedToTerms = $agreedToTerms;
        $this->_suspended = $suspended;
        $this->_changePasswordAtNextLogin = $changePasswordAtNextLogin;
    }

    /**
     * Retrieves a DOMElement which corresponds to this element and all
     * child properties.  This is used to build an entry back into a DOM
     * and eventually XML text for sending to the server upon updates, or
     * for application storage/persistence.
     *
     * @param DOMDocument $doc The DOMDocument used to construct DOMElements
     * @return DOMElement The DOMElement representing this element and all
     * child properties.
     */
    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_username !== null) {
            $element->setAttribute('userName', $this->_username);
        }
        if ($this->_password !== null) {
            $element->setAttribute('password', $this->_password);
        }
        if ($this->_hashFunctionName !== null) {
            $element->setAttribute('hashFunctionName', $this->_hashFunctionName);
        }
        if ($this->_admin !== null) {
            $element->setAttribute('admin', ($this->_admin ? "true" : "false"));
        }
        if ($this->_agreedToTerms !== null) {
            $element->setAttribute('agreedToTerms', ($this->_agreedToTerms ? "true" : "false"));
        }
        if ($this->_suspended !== null) {
            $element->setAttribute('suspended', ($this->_suspended ? "true" : "false"));
        }
        if ($this->_changePasswordAtNextLogin !== null) {
            $element->setAttribute('changePasswordAtNextLogin', ($this->_changePasswordAtNextLogin ? "true" : "false"));
        }

        return $element;
    }

    /**
     * Given a DOMNode representing an attribute, tries to map the data into
     * instance members.  If no mapping is defined, the name and value are
     * stored in an array.
     *
     * @param DOMNode $attribute The DOMNode attribute needed to be handled
     * @throws Zend_Gdata_App_InvalidArgumentException
     */
    protected function takeAttributeFromDOM($attribute)
    {
        switch ($attribute->localName) {
        case 'userName':
            $this->_username = $attribute->nodeValue;
            break;
        case 'password':
            $this->_password = $attribute->nodeValue;
            break;
        case 'hashFunctionName':
            $this->_hashFunctionName = $attribute->nodeValue;
            break;
        case 'admin':
            if ($attribute->nodeValue == "true") {
                $this->_admin = true;
            }
            else if ($attribute->nodeValue == "false") {
                $this->_admin = false;
            }
            else {
                #require_once('Zend/Gdata/App/InvalidArgumentException.php');
                throw new Zend_Gdata_App_InvalidArgumentException("Expected 'true' or 'false' for apps:login#admin.");
            }
            break;
        case 'agreedToTerms':
            if ($attribute->nodeValue == "true") {
                $this->_agreedToTerms = true;
            }
            else if ($attribute->nodeValue == "false") {
                $this->_agreedToTerms = false;
            }
            else {
                #require_once('Zend/Gdata/App/InvalidArgumentException.php');
                throw new Zend_Gdata_App_InvalidArgumentException("Expected 'true' or 'false' for apps:login#agreedToTerms.");
            }
            break;
        case 'suspended':
            if ($attribute->nodeValue == "true") {
                $this->_suspended = true;
            }
            else if ($attribute->nodeValue == "false") {
                $this->_suspended = false;
            }
            else {
                #require_once('Zend/Gdata/App/InvalidArgumentException.php');
                throw new Zend_Gdata_App_InvalidArgumentException("Expected 'true' or 'false' for apps:login#suspended.");
            }
            break;
        case 'changePasswordAtNextLogin':
            if ($attribute->nodeValue == "true") {
                $this->_changePasswordAtNextLogin = true;
            }
            else if ($attribute->nodeValue == "false") {
                $this->_changePasswordAtNextLogin = false;
            }
            else {
                #require_once('Zend/Gdata/App/InvalidArgumentException.php');
                throw new Zend_Gdata_App_InvalidArgumentException("Expected 'true' or 'false' for apps:login#changePasswordAtNextLogin.");
            }
            break;
        default:
            parent::takeAttributeFromDOM($attribute);
        }
    }

    /**
     * Get the value for this element's username attribute.
     *
     * @see setUsername
     * @return string The attribute being modified.
     */
    public function getUsername()
    {
        return $this->_username;
    }

    /**
     * Set the value for this element's username attribute. This string
     * is used to uniquely identify the user in this domian and is used
     * to form this user's email address.
     *
     * @param string $value The desired value for this attribute.
     * @return Zend_Gdata_Gapps_Extension_Login Provides a fluent interface.
     */
    public function setUsername($value)
    {
        $this->_username = $value;
        return $this;
    }

    /**
     * Get the value for this element's password attribute.
     *
     * @see setPassword
     * @return string The requested attribute.
     */
    public function getPassword()
    {
        return $this->_password;
    }

    /**
     * Set the value for this element's password attribute. As of this
     * writing, this can be either be provided as plaintext or hashed using
     * the SHA-1 algorithm for protection. If using a hash function,
     * this must be indicated by calling setHashFunctionName().
     *
     * @param string $value The desired value for this attribute.
     * @return Zend_Gdata_Gapps_Extension_Login Provides a fluent interface.
     */
    public function setPassword($value)
    {
        $this->_password = $value;
        return $this;
    }

    /**
     * Get the value for this element's hashFunctionName attribute.
     *
     * @see setHashFunctionName
     * @return string The requested attribute.
     */
    public function getHashFunctionName()
    {
        return $this->_hashFunctionName;
    }

    /**
     * Set the value for this element's hashFunctionName attribute. This
     * indicates whether the password supplied with setPassword() is in
     * plaintext or has had a hash function applied to it. If null,
     * plaintext is assumed. As of this writing, the only valid hash
     * function is 'SHA-1'.
     *
     * @param string $value The desired value for this attribute.
     * @return Zend_Gdata_Gapps_Extension_Login Provides a fluent interface.
     */
    public function setHashFunctionName($value)
    {
        $this->_hashFunctionName = $value;
        return $this;
    }

    /**
     * Get the value for this element's admin attribute.
     *
     * @see setAdmin
     * @return boolean The requested attribute.
     * @throws Zend_Gdata_App_InvalidArgumentException
     */
    public function getAdmin()
    {
        if (!(is_bool($this->_admin))) {
            #require_once('Zend/Gdata/App/InvalidArgumentException.php');
            throw new Zend_Gdata_App_InvalidArgumentException('Expected boolean for admin.');
        }
        return $this->_admin;
    }

    /**
     * Set the value for this element's admin attribute. This indicates
     * whether this user is an administrator for this domain.
     *
     * @param boolean $value The desired value for this attribute.
     * @return Zend_Gdata_Gapps_Extension_Login Provides a fluent interface.
     * @throws Zend_Gdata_App_InvalidArgumentException
     */
    public function setAdmin($value)
    {
        if (!(is_bool($value))) {
            #require_once('Zend/Gdata/App/InvalidArgumentException.php');
            throw new Zend_Gdata_App_InvalidArgumentException('Expected boolean for $value.');
        }
        $this->_admin = $value;
        return $this;
    }

    /**
     * Get the value for this element's agreedToTerms attribute.
     *
     * @see setAgreedToTerms
     * @return boolean The requested attribute.
     * @throws Zend_Gdata_App_InvalidArgumentException
     */
    public function getAgreedToTerms()
    {
        if (!(is_bool($this->_agreedToTerms))) {
            #require_once('Zend/Gdata/App/InvalidArgumentException.php');
            throw new Zend_Gdata_App_InvalidArgumentException('Expected boolean for agreedToTerms.');
        }
        return $this->_agreedToTerms;
    }

    /**
     * Set the value for this element's agreedToTerms attribute. This
     * indicates whether this user has agreed to the terms of service.
     *
     * @param boolean $value The desired value for this attribute.
     * @return Zend_Gdata_Gapps_Extension_Login Provides a fluent interface.
     * @throws Zend_Gdata_App_InvalidArgumentException
     */
    public function setAgreedToTerms($value)
    {
        if (!(is_bool($value))) {
            #require_once('Zend/Gdata/App/InvalidArgumentException.php');
            throw new Zend_Gdata_App_InvalidArgumentException('Expected boolean for $value.');
        }
        $this->_agreedToTerms = $value;
        return $this;
    }

    /**
     * Get the value for this element's suspended attribute.
     *
     * @see setSuspended
     * @return boolean The requested attribute.
     * @throws Zend_Gdata_App_InvalidArgumentException
     */
    public function getSuspended()
    {
        if (!(is_bool($this->_suspended))) {
            #require_once('Zend/Gdata/App/InvalidArgumentException.php');
            throw new Zend_Gdata_App_InvalidArgumentException('Expected boolean for suspended.');
        }
        return $this->_suspended;
    }

    /**
     * Set the value for this element's suspended attribute. If true, the
     * user will not be able to login to this domain until unsuspended.
     *
     * @param boolean $value The desired value for this attribute.
     * @return Zend_Gdata_Gapps_Extension_Login Provides a fluent interface.
     * @throws Zend_Gdata_App_InvalidArgumentException
     */
    public function setSuspended($value)
    {
        if (!(is_bool($value))) {
            #require_once('Zend/Gdata/App/InvalidArgumentException.php');
            throw new Zend_Gdata_App_InvalidArgumentException('Expected boolean for $value.');
        }
        $this->_suspended = $value;
        return $this;
    }

    /**
     * Get the value for this element's changePasswordAtNextLogin attribute.
     *
     * @see setChangePasswordAtNextLogin
     * @return boolean The requested attribute.
     * @throws Zend_Gdata_App_InvalidArgumentException
     */
    public function getChangePasswordAtNextLogin()
    {
        if (!(is_bool($this->_changePasswordAtNextLogin))) {
            #require_once('Zend/Gdata/App/InvalidArgumentException.php');
            throw new Zend_Gdata_App_InvalidArgumentException('Expected boolean for changePasswordAtNextLogin.');
        }
        return $this->_changePasswordAtNextLogin;
    }

    /**
     * Set the value for this element's changePasswordAtNextLogin attribute.
     * If true, the user will be forced to set a new password the next
     * time they login.
     *
     * @param boolean $value The desired value for this attribute.
     * @return Zend_Gdata_Gapps_Extension_Login Provides a fluent interface.
     * @throws Zend_Gdata_App_InvalidArgumentException
     */
    public function setChangePasswordAtNextLogin($value)
    {
        if (!(is_bool($value))) {
            #require_once('Zend/Gdata/App/InvalidArgumentException.php');
            throw new Zend_Gdata_App_InvalidArgumentException('Expected boolean for $value.');
        }
        $this->_changePasswordAtNextLogin = $value;
        return $this;
    }

    /**
     * Magic toString method allows using this directly via echo
     * Works best in PHP >= 4.2.0
     */
    public function __toString()
    {
        return "Username: " . $this->getUsername() .
            "\nPassword: " . (($this->getPassword() === null) ? "NOT SET" : "SET") .
            "\nPassword Hash Function: " . $this->getHashFunctionName() .
            "\nAdministrator: " . ($this->getAdmin() ? "Yes" : "No") .
            "\nAgreed To Terms: " . ($this->getAgreedToTerms() ? "Yes" : "No") .
            "\nSuspended: " . ($this->getSuspended() ? "Yes" : "No");
    }
}
