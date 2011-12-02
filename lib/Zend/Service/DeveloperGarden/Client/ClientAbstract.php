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
 * @version    $Id: ClientAbstract.php 22662 2010-07-24 17:37:36Z mabe $
 */

/**
 * @see Zend_Service_DeveloperGarden_Client_Soap
 */
#require_once 'Zend/Service/DeveloperGarden/Client/Soap.php';

/**
 * @see Zend_Service_DeveloperGarden_Credential
 */
#require_once 'Zend/Service/DeveloperGarden/Credential.php';

/**
 * @see Zend_Service_DeveloperGarden_SecurityTokenServer
 */
#require_once 'Zend/Service/DeveloperGarden/SecurityTokenServer.php';

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage DeveloperGarden
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @author     Marco Kaiser
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Service_DeveloperGarden_Client_ClientAbstract
{
    /**
     * constants for using with the odg api
     */
    const ENV_PRODUCTION = 1; // Production Environment
    const ENV_SANDBOX    = 2; // Sandbox Environment, limited access to the api
    const ENV_MOCK       = 3; // Api calls are without any functionality

    const PARTICIPANT_MUTE_OFF = 0; // removes mute from participant in a conference
    const PARTICIPANT_MUTE_ON  = 1; // mute participant in a conference
    const PARTICIPANT_RECALL   = 2; // recalls the participant in a conference

    /**
     * array of all possible env types
     *
     * @var int
     */
    static protected $_consts = null;

    /**
     * Available options
     *
     * @var array available options
     */
    protected $_options = array();

    /**
     * The service id to generate the auth service token
     *
     * @var string
     */
    protected $_serviceAuthId = 'https://odg.t-online.de';

    /**
     * Variable that holds the Zend_Service_DeveloperGarden env value
     *
     * @var int
     */
    protected $_serviceEnvironment = Zend_Service_DeveloperGarden_Client_ClientAbstract::ENV_PRODUCTION;

    /**
     * wsdl file
     *
     * @var string
     */
    protected $_wsdlFile = null;

    /**
     * the local wsdlFile
     *
     * @var string
     */
    protected $_wsdlFileLocal = null;

    /**
     * should we use the local wsdl file?
     *
     * @var boolean
     */
    protected $_useLocalWsdl = true;

    /**
     * class with credentials
     *
     * @var Zend_Service_DeveloperGarden_Credential
     */
    protected $_credential = null;

    /**
     * The internal Soap Client
     *
     * @var Zend_Soap_Client
     */
    protected $_soapClient = null;

    /**
     * array with options for classmapping
     *
     * @var array
     */
    protected $_classMap = array();

    /**
     * constructor
     *
     * @param array $options Associative array of options
     */
    public function __construct(array $options = array())
    {
        $this->_credential = new Zend_Service_DeveloperGarden_Credential();

        while (list($name, $value) = each($options)) {
            switch (ucfirst($name)) {
                case 'Username' :
                    $this->_credential->setUsername($value);
                    break;
                case 'Password' :
                    $this->_credential->setPassword($value);
                    break;
                case 'Realm' :
                    $this->_credential->setRealm($value);
                    break;
                case 'Environment' :
                    $this->setEnvironment($value);
            }
        }

        if (empty($this->_wsdlFile)) {
            #require_once 'Zend/Service/DeveloperGarden/Exception.php';
            throw new Zend_Service_DeveloperGarden_Exception('_wsdlFile not set for this service.');
        }

        if (!empty($this->_wsdlFileLocal)) {
            $this->_wsdlFileLocal = realpath(dirname(__FILE__) . '/../' . $this->_wsdlFileLocal);
        }

        if (empty($this->_wsdlFileLocal) || $this->_wsdlFileLocal === false) {
            #require_once 'Zend/Service/DeveloperGarden/Exception.php';
            throw new Zend_Service_DeveloperGarden_Exception('_wsdlFileLocal not set for this service.');
        }
    }

    /**
     * Set an option
     *
     * @param  string $name
     * @param  mixed $value
     * @throws Zend_Service_DeveloperGarden_Client_Exception
     * @return Zend_Service_DeveloperGarden_Client_ClientAbstract
     */
    public function setOption($name, $value)
    {
        if (!is_string($name)) {
            #require_once 'Zend/Service/DeveloperGarden/Client/Exception.php';
            throw new Zend_Service_DeveloperGarden_Client_Exception('Incorrect option name: ' . $name);
        }
        $name = strtolower($name);
        if (array_key_exists($name, $this->_options)) {
            $this->_options[$name] = $value;
        }

        return $this;
    }

    /**
     * get an option value from the internal options object
     *
     * @param  string $name
     * @return mixed
     */
    public function getOption($name)
    {
        $name = strtolower($name);
        if (array_key_exists($name, $this->_options)) {
            return $this->_options[$name];
        }

        return null;
    }

    /**
     * returns the internal soap client
     * if not allready exists we create an instance of
     * Zend_Soap_Client
     *
     * @final
     * @return Zend_Service_DeveloperGarden_Client_Soap
     */
    final public function getSoapClient()
    {
        if ($this->_soapClient === null) {
            /**
             * init the soapClient
             */
            $this->_soapClient = new Zend_Service_DeveloperGarden_Client_Soap(
                $this->getWsdl(),
                $this->getClientOptions()
            );
            $this->_soapClient->setCredential($this->_credential);
            $tokenService = new Zend_Service_DeveloperGarden_SecurityTokenServer(
                array(
                    'username'    => $this->_credential->getUsername(),
                    'password'    => $this->_credential->getPassword(),
                    'environment' => $this->getEnvironment(),
                    'realm'       => $this->_credential->getRealm(),
                )
            );
            $this->_soapClient->setTokenService($tokenService);
        }

        return $this->_soapClient;
    }

    /**
     * sets new environment
     *
     * @param int $environment
     * @return Zend_Service_DeveloperGarden_Client_ClientAbstract
     */
    public function setEnvironment($environment)
    {
        self::checkEnvironment($environment);
        $this->_serviceEnvironment = $environment;
        return $this;
    }

    /**
     * returns the current configured environemnt
     *
     * @return int
     */
    public function getEnvironment()
    {
        return $this->_serviceEnvironment;
    }

    /**
     * returns the wsdl file path, a uri or the local path
     *
     * @return string
     */
    public function getWsdl()
    {
        if ($this->_useLocalWsdl) {
            $retVal = $this->_wsdlFileLocal;
        } else {
            $retVal = $this->_wsdlFile;
        }

        return $retVal;
    }

    /**
     * switch to the local wsdl file usage
     *
     * @param boolen $use
     * @return Zend_Service_DeveloperGarden_Client_ClientAbstract
     */
    public function setUseLocalWsdl($use = true)
    {
        $this->_useLocalWsdl = (boolean) $use;
        return $this;
    }

    /**
     * sets a new wsdl file
     *
     * @param string $wsdlFile
     * @return Zend_Service_DeveloperGarden_Client_ClientAbstract
     */
    public function setWsdl($wsdlFile = null)
    {
        if (empty($wsdlFile)) {
            #require_once 'Zend/Service/DeveloperGarden/Exception.php';
            throw new Zend_Service_DeveloperGarden_Exception('_wsdlFile not set for this service.');
        }
        $this->_wsdlFile = $wsdlFile;
        return $this;
    }

    /**
     * sets a new local wsdl file
     *
     * @param string $wsdlFile
     * @return Zend_Service_DeveloperGarden_Client_ClientAbstract
     */
    public function setLocalWsdl($wsdlFile = null)
    {
        if (empty($wsdlFile)) {
            #require_once 'Zend/Service/DeveloperGarden/Exception.php';
            throw new Zend_Service_DeveloperGarden_Exception('_wsdlFileLocal not set for this service.');
        }
        $this->_wsdlFileLocal = $wsdlFile;
        return $this;
    }

    /**
     * returns an array with configured options for this client
     *
     * @return array
     */
    public function getClientOptions()
    {
        $options = array(
            'soap_version' => SOAP_1_1,
        );
        if (!empty($this->_classMap)) {
            $options['classmap'] = $this->_classMap;
        }
        $wsdlCache = Zend_Service_DeveloperGarden_SecurityTokenServer_Cache::getWsdlCache();
        if ($wsdlCache !== null) {
            $options['cache_wsdl'] = $wsdlCache;
        }
        return $options;
    }

    /**
     * returns the internal credential object
     *
     * @return Zend_Service_DeveloperGarden_Credential
     */
    public function getCredential()
    {
        return $this->_credential;
    }

    /**
     * helper method to create const arrays
     * @return null
     */
    static protected function _buildConstArray()
    {
        $r = new ReflectionClass(__CLASS__);
        foreach ($r->getConstants() as $k => $v) {
            $s = explode('_', $k, 2);
            if (!isset(self::$_consts[$s[0]])) {
                self::$_consts[$s[0]] = array();
            }
            self::$_consts[$s[0]][$v] = $k;
        }
    }

    /**
     * returns an array of all available environments
     *
     * @return array
     */
    static public function getParticipantActions()
    {
        if (empty(self::$_consts)) {
            self::_buildConstArray();
        }
        return self::$_consts['PARTICIPANT'];
    }

    /**
     * checks if the given action is valid
     * otherwise it @throws Zend_Service_DeveloperGarden_Exception
     *
     * @param int $action
     * @throws Zend_Service_DeveloperGarden_Client_Exception
     * @return void
     */
    static public function checkParticipantAction($action)
    {
        if (!array_key_exists($action, self::getParticipantActions())) {
            #require_once 'Zend/Service/DeveloperGarden/Client/Exception.php';
            throw new Zend_Service_DeveloperGarden_Client_Exception(
                'Wrong Participant Action ' . $action . ' supplied.'
            );
        }
    }

    /**
     * returns an array of all available environments
     *
     * @return array
     */
    static public function getEnvironments()
    {
        if (empty(self::$_consts)) {
            self::_buildConstArray();
        }
        return self::$_consts['ENV'];
    }

    /**
     * checks if the given environemnt is valid
     * otherwise it @throws Zend_Service_DeveloperGarden_Client_Exception
     *
     * @param int $environment
     * @throws Zend_Service_DeveloperGarden_Client_Exception
     * @return void
     */
    static public function checkEnvironment($environment)
    {
        if (!array_key_exists($environment, self::getEnvironments())) {
            #require_once 'Zend/Service/DeveloperGarden/Client/Exception.php';
            throw new Zend_Service_DeveloperGarden_Client_Exception(
                'Wrong environment ' . $environment . ' supplied.'
            );
        }
    }
}
