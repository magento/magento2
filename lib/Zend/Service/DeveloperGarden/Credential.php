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
 * @package    Zend_Service
 * @subpackage DeveloperGarden
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Credential.php 20166 2010-01-09 19:00:17Z bkarwin $
 */

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage DeveloperGarden
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @author     Marco Kaiser
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_DeveloperGarden_Credential
{
    /**
     * Service Auth Username
     *
     * @var string
     */
    protected $_username = null;

    /**
     * Service Password
     *
     * @var string
     */
    protected $_password = null;

    /**
     * Service Realm - default t-online.de
     *
     * @var string
     */
    protected $_realm = 't-online.de';

    /**
     * constructor to init the internal data
     *
     * @param string $username
     * @param string $password
     * @param string $realm
     * @return Zend_Service_DeveloperGarden_Credential
     */
    public function __construct($username = null, $password = null, $realm = null)
    {
        if (!empty($username)) {
            $this->setUsername($username);
        }
        if (!empty($password)) {
            $this->setPassword($password);
        }
        if (!empty($realm)) {
            $this->setRealm($realm);
        }
    }

    /**
     * split the password into an array
     *
     * @param string $password
     * @throws Zend_Service_DeveloperGarden_Client_Exception
     * @return Zend_Service_DeveloperGarden_Client_ClientAbstract
     */
    public function setPassword($password = null)
    {
        if (empty($password)) {
            #require_once 'Zend/Service/DeveloperGarden/Client/Exception.php';
            throw new Zend_Service_DeveloperGarden_Client_Exception('Empty password not permitted.');
        }

        if (!is_string($password)) {
            #require_once 'Zend/Service/DeveloperGarden/Client/Exception.php';
            throw new Zend_Service_DeveloperGarden_Client_Exception('Password must be a string.');
        }

        $this->_password = $password;
        return $this;
    }

    /**
     * returns the current configured password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->_password;
    }

    /**
     * set the new login
     *
     * @param string $username
     * @throws Zend_Service_DeveloperGarden_Client_Exception
     * @return Zend_Service_DeveloperGarden_Client_ClientAbstract
     */
    public function setUsername($username = null)
    {
        if (empty($username)) {
            #require_once 'Zend/Service/DeveloperGarden/Client/Exception.php';
            throw new Zend_Service_DeveloperGarden_Client_Exception('Empty username not permitted.');
        }

        if (!is_string($username)) {
            #require_once 'Zend/Service/DeveloperGarden/Client/Exception.php';
            throw new Zend_Service_DeveloperGarden_Client_Exception('Username must be a string.');
        }

        $this->_username = $username;
        return $this;
    }

    /**
     * returns the username
     *
     * if $withRealm == true we combine username and realm like
     * username@realm
     *
     * @param $boolean withRealm
     * @return string|null
     */
    public function getUsername($withRealm = false)
    {
        $retValue = $this->_username;
        if ($withRealm) {
            $retValue = sprintf(
                '%s@%s',
                $this->_username,
                $this->_realm
            );
        }
        return $retValue;
    }

    /**
     * set the new realm
     *
     * @param string $realm
     * @throws Zend_Service_DeveloperGarden_Client_Exception
     * @return Zend_Service_DeveloperGarden_Client_ClientAbstract
     */
    public function setRealm($realm = null)
    {
        if (empty($realm)) {
            #require_once 'Zend/Service/DeveloperGarden/Client/Exception.php';
            throw new Zend_Service_DeveloperGarden_Client_Exception('Empty realm not permitted.');
        }

        if (!is_string($realm)) {
            #require_once 'Zend/Service/DeveloperGarden/Client/Exception.php';
            throw new Zend_Service_DeveloperGarden_Client_Exception('Realm must be a string.');
        }

        $this->_realm = $realm;
        return $this;
    }

    /**
     * returns the realm
     *
     * @return string|null
     */
    public function getRealm()
    {
        return $this->_realm;
    }
}

