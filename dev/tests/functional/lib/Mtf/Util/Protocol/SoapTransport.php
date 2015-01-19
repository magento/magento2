<?php
/**
 * @spi
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mtf\Util\Protocol;

class SoapTransport
{
    /**
     * @var \SoapClient
     */
    protected $_soap;

    /**
     * @var array
     */
    protected $_configuration;

    /**
     * Construct
     */
    public function __construct(array $configuration)
    {
        $this->_configuration = $configuration;

        $wsdl = $_ENV['app_frontend_url'] . $configuration['wsdl'];

        $this->_soap = new \SoapClient($wsdl, ['soap_version' => SOAP_1_2]);
    }

    /**
     * Login and returning sessionId
     * @return string
     */
    protected function _getSessionId()
    {
        //@todo. What if user needs to use different credentials?
        $credentials = $this->_configuration['auth_credentials'];
        return $this->_soap->login($credentials['username'], $credentials['apiKey']);
    }

    /**
     * Call resource functionality
     *
     * @param string $method
     * @param array $params
     * @return mixed
     */
    public function call($method, $params)
    {
        $params[$this->_configuration['auth_token_name']] = $this->_getSessionId();
        return call_user_func_array([$this->_soap, $method], $params);
    }
}
