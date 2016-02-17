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
 * @subpackage StrikeIron
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/**
 * @see Zend_Service_StrikeIron_Decorator
 */
#require_once 'Zend/Service/StrikeIron/Decorator.php';


/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage StrikeIron
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_StrikeIron_Base
{
    /**
     * Configuration options
     * @param array
     */
    protected $_options = array('username' => null,
                                'password' => null,
                                'client'   => null,
                                'options'  => null,
                                'headers'  => null,
                                'wsdl'     => null);

    /**
     * Output headers returned by the last call to SOAPClient->__soapCall()
     * @param array
     */
    protected $_outputHeaders = array();

    /**
     * Class constructor
     *
     * @param  array  $options  Key/value pair options
     * @throws Zend_Service_StrikeIron_Exception
     */
    public function __construct($options = array())
    {
        if (!extension_loaded('soap')) {
            /**
             * @see Zend_Service_StrikeIron_Exception
             */
            #require_once 'Zend/Service/StrikeIron/Exception.php';
            throw new Zend_Service_StrikeIron_Exception('SOAP extension is not enabled');
        }

        $this->_options  = array_merge($this->_options, $options);

        $this->_initSoapHeaders();
        $this->_initSoapClient();
    }

    /**
     * Proxy method calls to the SOAPClient instance, transforming method
     * calls and responses for convenience.
     *
     * @param  string  $method  Method name
     * @param  array   $params  Parameters for method
     * @return mixed            Result
     * @throws Zend_Service_StrikeIron_Exception
     */
    public function __call($method, $params)
    {
        // prepare method name and parameters for soap call
        list($method, $params) = $this->_transformCall($method, $params);
        $params = isset($params[0]) ? array($params[0]) : array();

        // make soap call, capturing the result and output headers
        try {
            $result = $this->_options['client']->__soapCall($method,
                                                            $params,
                                                            $this->_options['options'],
                                                            $this->_options['headers'],
                                                            $this->_outputHeaders);
        } catch (Exception $e) {
            $message = get_class($e) . ': ' . $e->getMessage();
            /**
             * @see Zend_Service_StrikeIron_Exception
             */
            #require_once 'Zend/Service/StrikeIron/Exception.php';
            throw new Zend_Service_StrikeIron_Exception($message, $e->getCode(), $e);
        }

        // transform/decorate the result and return it
        $result = $this->_transformResult($result, $method, $params);
        return $result;
    }

    /**
     * Initialize the SOAPClient instance
     *
     * @return void
     */
    protected function _initSoapClient()
    {
        if (! isset($this->_options['options'])) {
            $this->_options['options'] = array();
        }

        if (! isset($this->_options['client'])) {
            $this->_options['client'] = new SoapClient($this->_options['wsdl'],
                                                       $this->_options['options']);
        }
    }

    /**
     * Initialize the headers to pass to SOAPClient->__soapCall()
     *
     * @return void
     * @throws Zend_Service_StrikeIron_Exception
     */
    protected function _initSoapHeaders()
    {
        // validate headers and check if LicenseInfo was given
        $foundLicenseInfo = false;
        if (isset($this->_options['headers'])) {
            if (! is_array($this->_options['headers'])) {
                $this->_options['headers'] = array($this->_options['headers']);
            }

            foreach ($this->_options['headers'] as $header) {
                if (! $header instanceof SoapHeader) {
                    /**
                     * @see Zend_Service_StrikeIron_Exception
                     */
                    #require_once 'Zend/Service/StrikeIron/Exception.php';
                    throw new Zend_Service_StrikeIron_Exception('Header must be instance of SoapHeader');
                } else if ($header->name == 'LicenseInfo') {
                    $foundLicenseInfo = true;
                    break;
                }
            }
        } else {
            $this->_options['headers'] = array();
        }

        // add default LicenseInfo header if a custom one was not supplied
        if (! $foundLicenseInfo) {
            $this->_options['headers'][] = new SoapHeader('http://ws.strikeiron.com',
                            'LicenseInfo',
                            array('RegisteredUser' => array('UserID'   => $this->_options['username'],
                                                            'Password' => $this->_options['password'])));
        }
    }

    /**
     * Transform a method name or method parameters before sending them
     * to the remote service.  This can be useful for inflection or other
     * transforms to give the method call a more PHP-like interface.
     *
     * @see    __call()
     * @param  string  $method  Method name called from PHP
     * @param  mixed   $param   Parameters passed from PHP
     * @return array            [$method, $params] for SOAPClient->__soapCall()
     */
    protected function _transformCall($method, $params)
    {
        return array(ucfirst($method), $params);
    }

    /**
     * Transform the result returned from a method before returning
     * it to the PHP caller.  This can be useful for transforming
     * the SOAPClient returned result to be more PHP-like.
     *
     * The $method name and $params passed to the method are provided to
     * allow decisions to be made about how to transform the result based
     * on what was originally called.
     *
     * @see    __call()
     * @param  object $result  Raw result returned from SOAPClient_>__soapCall()
     * @param  string $method  Method name that was passed to SOAPClient->__soapCall()
     * @param  array  $params  Method parameters that were passed to SOAPClient->__soapCall()
     * @return mixed  Transformed result
     */
    protected function _transformResult($result, $method, $params)
    {
        $resultObjectName = "{$method}Result";
        if (isset($result->$resultObjectName)) {
            $result = $result->$resultObjectName;
        }
        if (is_object($result)) {
            $result = new Zend_Service_StrikeIron_Decorator($result, $resultObjectName);
        }
        return $result;
    }

    /**
     * Get the WSDL URL for this service.
     *
     * @return string
     */
    public function getWsdl()
    {
        return $this->_options['wsdl'];
    }

    /**
     * Get the SOAP Client instance for this service.
     */
    public function getSoapClient()
    {
        return $this->_options['client'];
    }

    /**
     * Get the StrikeIron output headers returned with the last method response.
     *
     * @return array
     */
    public function getLastOutputHeaders()
    {
        return $this->_outputHeaders;
    }

    /**
     * Get the StrikeIron subscription information for this service.
     * If any service method was recently called, the subscription info
     * should have been returned in the SOAP headers so it is cached
     * and returned from the cache.  Otherwise, the getRemainingHits()
     * method is called as a dummy to get the subscription info headers.
     *
     * @param  boolean  $now          Force a call to getRemainingHits instead of cache?
     * @param  string   $queryMethod  Method that will cause SubscriptionInfo header to be sent
     * @return Zend_Service_StrikeIron_Decorator  Decorated subscription info
     * @throws Zend_Service_StrikeIron_Exception
     */
    public function getSubscriptionInfo($now = false, $queryMethod = 'GetRemainingHits')
    {
        if ($now || empty($this->_outputHeaders['SubscriptionInfo'])) {
            $this->$queryMethod();
        }

        // capture subscription info if returned in output headers
        if (isset($this->_outputHeaders['SubscriptionInfo'])) {
            $info = (object)$this->_outputHeaders['SubscriptionInfo'];
            $subscriptionInfo = new Zend_Service_StrikeIron_Decorator($info, 'SubscriptionInfo');
        } else {
            $msg = 'No SubscriptionInfo header found in last output headers';
            /**
             * @see Zend_Service_StrikeIron_Exception
             */
            #require_once 'Zend/Service/StrikeIron/Exception.php';
            throw new Zend_Service_StrikeIron_Exception($msg);
        }

        return $subscriptionInfo;
    }
}
