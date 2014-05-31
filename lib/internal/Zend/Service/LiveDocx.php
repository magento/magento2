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
 * @subpackage LiveDocx
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: LiveDocx.php 23022 2010-10-05 15:30:55Z jonathan_maron $
 */

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage LiveDocx
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @since      LiveDocx 1.0
 */
class Zend_Service_LiveDocx
{
    /**
     * LiveDocx service version
     * @since LiveDocx 1.0
     */
    const VERSION = '2.0';

    /**
     * SOAP client used to connect to LiveDocx service
     * @var   Zend_Soap_Client
     * @since LiveDocx 1.0
     */
    protected $_soapClient;
        
    /**
     * WSDL of LiveDocx web service
     * @var   string
     * @since LiveDocx 1.0
     */
    protected $_wsdl;
        
    /**
     * Array of credentials (username and password) to log into backend server
     * @var   array
     * @since LiveDocx 1.2
     */
    protected $_credentials;
    
    /**
     * Set to true, when session is logged into backend server
     * @var   boolean
     * @since LiveDocx 1.2
     */
    protected $_loggedIn;
    
    /**
     * Constructor
     *
     * Optionally, pass an array of options (or Zend_Config object).
     * 
     * If an option with the key 'soapClient' is provided, that value will be 
     * used to set the internal SOAP client used to connect to the LiveDocx
     * service.
     * 
     * Use 'soapClient' in the case that you have a dedicated or (locally
     * installed) licensed LiveDocx server. For example:
     *
     * {code}
     * $phpLiveDocx = new Zend_Service_LiveDocx_MailMerge(
     *     array (
     *         'username'   => 'myUsername',
     *         'password'   => 'myPassword',
     *         'soapClient' => new Zend_Soap_Client('https://api.example.com/path/mailmerge.asmx?WSDL')
     *     )
     * );
     * {code}
     * 
     * Replace the URI of the WSDL in the constructor of Zend_Soap_Client with
     * that of your dedicated or licensed LiveDocx server.
     *
     * If you are using the public LiveDocx server, simply pass 'username' and
     * 'password'. For example:
     *
     * {code}
     * $phpLiveDocx = new Zend_Service_LiveDocx_MailMerge(
     *     array (
     *         'username' => 'myUsername',
     *         'password' => 'myPassword'
     *     )
     * );
     * {code}
     * 
     * If you prefer to not pass the username and password through the
     * constructor, you can also call the following methods:
     * 
     * {code}
     * $phpLiveDocx = new Zend_Service_LiveDocx_MailMerge();
     * 
     * $phpLiveDocx->setUsername('myUsername')
     *             ->setPassword('myPassword');
     * {/code}
     * 
     * Or, if you want to specify your own SoapClient:
     * 
     * {code}
     * $phpLiveDocx = new Zend_Service_LiveDocx_MailMerge();
     * 
     * $phpLiveDocx->setUsername('myUsername')
     *             ->setPassword('myPassword');
     * 
     * $phpLiveDocx->setSoapClient(
     *     new Zend_Soap_Client('https://api.example.com/path/mailmerge.asmx?WSDL')
     * );
     * {/code} 
     *
     * @param  array|Zend_Config $options
     * @return void
     * @throws Zend_Service_LiveDocx_Exception
     * @since  LiveDocx 1.0
     */    
    public function __construct($options = null)
    {
        $this->_credentials = array();
        $this->_loggedIn = false;
        
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }
        
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }
    
    /**
     * Set options
     * One or more of username, password, soapClient
     * 
     * @param  $options
     * @return Zend_Service_LiveDocx
     * @since  LiveDocx 1.2
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            $method = 'set' . $key;
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
        
        return $this;
    }
        
    /**
     * Clean up and log out of LiveDocx service
     *
     * @return boolean
     * @since  LiveDocx 1.0
     */
    public function __destruct()
    {
        return $this->logOut();
    }
    
    /**
     * Init Soap client - connect to SOAP service
     *
     * @param  string $endpoint
     * @throws Zend_Service_LiveDocx_Exception
     * @return void
     * @since  LiveDocx 1.2
     */
    protected function _initSoapClient($endpoint)
    {
        try {
            #require_once 'Zend/Soap/Client.php';
            $this->_soapClient = new Zend_Soap_Client();
            $this->_soapClient->setWsdl($endpoint);                
        } catch (Zend_Soap_Client_Exception $e) {
            #require_once 'Zend/Service/LiveDocx/Exception.php';
            throw new Zend_Service_LiveDocx_Exception('Cannot connect to LiveDocx service at ' . $endpoint, 0, $e);
        }            
    }
    
    /**
     * Get SOAP client
     *
     * @return Zend_Soap_Client
     * @since  LiveDocx 1.2
     */
    public function getSoapClient()
    {
        return $this->_soapClient;
    }
    
    /**
     * Set SOAP client
     *
     * @param  Zend_Soap_Client $soapClient
     * @return Zend_Service_LiveDocx
     * @since  LiveDocx 1.2
     */
    public function setSoapClient(Zend_Soap_Client $soapClient)
    {
        $this->_soapClient = $soapClient;
        return $this;
    }

    /**
     * Log in to LiveDocx service
     *
     * @param string $username
     * @param string $password
     *
     * @throws Zend_Service_LiveDocx_Exception
     * @return boolean
     * @since  LiveDocx 1.2
     */
    public function logIn()
    {
        if (!$this->isLoggedIn()) {
            if (null === $this->getUsername()) {
                #require_once 'Zend/Service/LiveDocx/Exception.php';
                throw new Zend_Service_LiveDocx_Exception(
                    'Username has not been set. To set username specify the options array in the constructor or call setUsername($username) after instantiation'
                );
            }
            
            if (null === $this->getPassword()) {
                #require_once 'Zend/Service/LiveDocx/Exception.php';
                throw new Zend_Service_LiveDocx_Exception(
                    'Password has not been set. To set password specify the options array in the constructor or call setPassword($password) after instantiation'
                );
            }
            
            if (null === $this->getSoapClient()) {
                $this->_initSoapClient($this->_wsdl);
            }            
            
            try {
                $this->getSoapClient()->LogIn(array(
                    'username' => $this->getUsername(),
                    'password' => $this->getPassword(),
                ));
                $this->_loggedIn = true;
            } catch (Exception $e) {
                #require_once 'Zend/Service/LiveDocx/Exception.php';
                throw new Zend_Service_LiveDocx_Exception(
                    'Cannot login into LiveDocx service - username and/or password are invalid', 0, $e
                );
            }            
        }
        
        return $this->_loggedIn;
    }

    /**
     * Log out of the LiveDocx service
     *
     * @throws Zend_Service_LiveDocx_Exception
     * @return boolean
     * @since  LiveDocx 1.2
     */
    public function logOut()
    {
        if ($this->isLoggedIn()) {
            try {
                $this->getSoapClient()->LogOut();
                $this->_loggedIn = false;
            } catch (Exception $e) {
                #require_once 'Zend/Service/LiveDocx/Exception.php';
                throw new Zend_Service_LiveDocx_Exception(
                    'Cannot log out of LiveDocx service', 0, $e
                );
            }            
        }
        
        return $this->_loggedIn;
    }
    
    /**
     * Return true, if session is currently logged into the backend server
     * 
     * @return boolean
     * @since  LiveDocx 1.2
     */
    public function isLoggedIn()
    {
        return $this->_loggedIn;
    }
    
    /**
     * Set username
     * 
     * @return Zend_Service_LiveDocx
     * @since  LiveDocx 1.0
     */
    public function setUsername($username)
    {
        $this->_credentials['username'] = $username;
        return $this;
    }
    
    /**
     * Set password
     * 
     * @return Zend_Service_LiveDocx
     * @since  LiveDocx 1.0
     */    
    public function setPassword($password)
    {
        $this->_credentials['password'] = $password;
        return $this;
    }

    /**
     * Set WSDL of LiveDocx web service
     * 
     * @return Zend_Service_LiveDocx
     * @since  LiveDocx 1.0
     */      
    public function setWsdl($wsdl) 
    {
        $this->_wsdl = $wsdl;
        return $this;
    }
      
    /**
     * Return current username
     * 
     * @return string|null
     * @since  LiveDocx 1.0
     */
    public function getUsername()
    {
        if (isset($this->_credentials['username'])) {
            return $this->_credentials['username'];
        }
        
        return null;
    }
    
    /**
     * Return current password
     * 
     * @return string|null
     * @since  LiveDocx 1.0
     */    
    public function getPassword()
    {
        if (isset($this->_credentials['password'])) {
            return $this->_credentials['password'];
        }
        
        return null; 
    }
    
    /**
     * Return WSDL of LiveDocx web service
     * 
     * @return Zend_Service_LiveDocx
     * @since  LiveDocx 1.0
     */      
    public function getWsdl() 
    {
        return $this->_wsdl;
    }    

    /**
     * Return the document format (extension) of a filename
     *
     * @param  string $filename
     * @return string
     * @since  LiveDocx 1.0
     */
    public function getFormat($filename)
    {
        return strtolower(substr(strrchr($filename, '.'), 1));
    }
    
    /**
     * Return the current API version
     *
     * @return string
     * @since  LiveDocx 1.0
     */
    public function getVersion()
    {
        return self::VERSION;
    }
    
    /**
     * Compare the current API version with another version
     *
     * @param  string $version (STRING NOT FLOAT)
     * @return int -1 (version is less than API version), 0 (versions are equal), or 1 (version is greater than API version)
     * @since  LiveDocx 1.0
     */
    public function compareVersion($version)
    {
        return version_compare($version, $this->getVersion());
    }
}