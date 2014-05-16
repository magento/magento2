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
 * @spi
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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

        $this->_soap = new \SoapClient($wsdl, array('soap_version' => SOAP_1_2));
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
        return call_user_func_array(array($this->_soap, $method), $params);
    }
}
