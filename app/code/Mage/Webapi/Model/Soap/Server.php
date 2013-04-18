<?php
/**
 * Magento-specific SOAP server.
 * TODO: Remove dependency on Zend SOAP Server and methods overrides. Create Magento_Soap_Server instead.
 * TODO: Remove dependence on application config, probably move it to dispatcher.
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webapi_Model_Soap_Server extends \Zend\Soap\Server
{
    const SOAP_DEFAULT_ENCODING = 'UTF-8';

    /**#@+
     * Path in config to Webapi settings.
     */
    const CONFIG_PATH_WSDL_CACHE_ENABLED = 'webapi/soap/wsdl_cache_enabled';
    const CONFIG_PATH_SOAP_CHARSET = 'webapi/soap/charset';
    /**#@-*/

    const REQUEST_PARAM_RESOURCES = 'resources';
    const REQUEST_PARAM_WSDL = 'wsdl';

    /** @var Mage_Core_Model_Store */
    protected $_application;

    /** @var Magento_DomDocument_Factory */
    protected $_domDocumentFactory;

    /** @var Mage_Webapi_Controller_Request_Soap */
    protected $_request;

    /**
     * Initialize dependencies.
     *
     * @param Mage_Core_Model_App $application
     * @param Mage_Webapi_Controller_Request_Soap $request
     * @param Magento_DomDocument_Factory $domDocumentFactory
     */
    public function __construct(
        Mage_Core_Model_App $application,
        Mage_Webapi_Controller_Request_Soap $request,
        Magento_DomDocument_Factory $domDocumentFactory
    ) {
        parent::__construct();

        $this->_application = $application;
        $this->_request = $request;
        $this->_domDocumentFactory = $domDocumentFactory;
    }

    /**
     * Process Webapi SOAP fault.
     *
     * @param Mage_Webapi_Model_Soap_Fault|Exception|string $fault
     * @param string $code
     * @return SoapFault|string
     */
    public function fault($fault = null, $code = null)
    {
        if ($fault instanceof Mage_Webapi_Model_Soap_Fault) {
            return $fault->toXml($this->_application->isDeveloperMode());
        } else {
            return parent::fault($fault, $code);
        }
    }

    /**
     * Catch exceptions if request is invalid and output fault message.
     *
     * @param DOMDocument|DOMNode|SimpleXMLElement|stdClass|string $request
     * @return Mage_Webapi_Model_Soap_Server
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    protected function _setRequest($request)
    {
        try {
            parent::_setRequest($request);
        } catch (Exception $e) {
            $fault = new Mage_Webapi_Model_Soap_Fault(
                $e->getMessage(),
                Mage_Webapi_Model_Soap_Fault::FAULT_CODE_SENDER
            );
            die($fault->toXml($this->_application->isDeveloperMode()));
        }
        return $this;
    }

    /**
     * Suppress PHP error output because it has already been displayed by SoapServer extension.
     * TODO: remove this method when removing dependence on Zend/Soap/Server
     *
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     * @param array $errcontext
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    public function handlePhpErrors($errno, $errstr, $errfile = null, $errline = null, array $errcontext = null)
    {
        die();
    }

    /**
     * Get SOAP Header names from request.
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function getRequestHeaders()
    {
        $dom = $this->_domDocumentFactory->createDomDocument();
        $dom->loadXML($this->getLastRequest());
        $headers = array();
        /** @var DOMElement $header */
        foreach ($dom->getElementsByTagName('Header')->item(0)->childNodes as $header) {
            list($headerNs, $headerName) = explode(":", $header->nodeName);
            $headers[] = $headerName;
        }

        return $headers;
    }

    /**
     * Enable or disable SOAP extension WSDL cache depending on Magento configuration.
     */
    public function initWsdlCache()
    {
        $wsdlCacheEnabled = (bool)$this->_application->getStore()->getConfig(self::CONFIG_PATH_WSDL_CACHE_ENABLED);
        if ($wsdlCacheEnabled) {
            ini_set('soap.wsdl_cache_enabled', '1');
        } else {
            ini_set('soap.wsdl_cache_enabled', '0');
        }
    }

    /**
     * Retrieve charset used in SOAP API.
     *
     * @return string
     */
    public function getApiCharset()
    {
        $charset = $this->_application->getStore()->getConfig(self::CONFIG_PATH_SOAP_CHARSET);
        return $charset ? $charset : Mage_Webapi_Model_Soap_Server::SOAP_DEFAULT_ENCODING;
    }

    /**
     * Get SOAP endpoint URL.
     *
     * @param bool $isWsdl
     * @return string
     */
    public function generateUri($isWsdl = false)
    {
        $params = array(
            self::REQUEST_PARAM_RESOURCES => $this->_request->getRequestedResources()
        );
        if ($isWsdl) {
            $params[self::REQUEST_PARAM_WSDL] = true;
        }
        $query = http_build_query($params, '', '&');
        return $this->getEndpointUri() . '?' . $query;
    }

    /**
     * Generate URI of SOAP endpoint.
     *
     * @return string
     */
    public function getEndpointUri()
    {
        // @TODO: Implement proper endpoint URL retrieval mechanism in APIA-718 story
        return $this->_application->getStore()->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB)
            . $this->_application->getConfig()->getAreaFrontName() . '/'
            . Mage_Webapi_Controller_Front::API_TYPE_SOAP;
    }
}
