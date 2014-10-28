<?php
/**
 * Magento-specific SOAP server.
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webapi\Model\Soap;

class Server
{
    const SOAP_DEFAULT_ENCODING = 'UTF-8';

    /**#@+
     * Path in config to Webapi settings.
     */
    const CONFIG_PATH_WSDL_CACHE_ENABLED = 'webapi/soap/wsdl_cache_enabled';

    const CONFIG_PATH_SOAP_CHARSET = 'webapi/soap/charset';

    /**#@-*/
    const REQUEST_PARAM_SERVICES = 'services';

    const REQUEST_PARAM_WSDL = 'wsdl';

    /**
     * @var \Magento\Framework\App\AreaLIst
     */
    protected $_areaList;

    /**
     * @var \Magento\Framework\Config\ScopeInterface
     */
    protected $_configScope;

    /** @var \Magento\Framework\DomDocument\Factory */
    protected $_domDocumentFactory;

    /** @var \Magento\Webapi\Controller\Soap\Request */
    protected $_request;

    /** @var \Magento\Framework\StoreManagerInterface */
    protected $_storeManager;

    /** @var \Magento\Webapi\Model\Soap\Server\Factory */
    protected $_soapServerFactory;

    /** @var \Magento\Webapi\Model\Config\ClassReflector\TypeProcessor */
    protected $_typeProcessor;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Initialize dependencies, initialize WSDL cache.
     *
     * @param \Magento\Framework\App\AreaList $areaList
     * @param \Magento\Framework\Config\ScopeInterface $configScope
     * @param \Magento\Webapi\Controller\Soap\Request $request
     * @param \Magento\Framework\DomDocument\Factory $domDocumentFactory
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Webapi\Model\Soap\Server\Factory $soapServerFactory
     * @param \Magento\Webapi\Model\Config\ClassReflector\TypeProcessor $typeProcessor
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @throws \Magento\Webapi\Exception
     */
    public function __construct(
        \Magento\Framework\App\AreaList $areaList,
        \Magento\Framework\Config\ScopeInterface $configScope,
        \Magento\Webapi\Controller\Soap\Request $request,
        \Magento\Framework\DomDocument\Factory $domDocumentFactory,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Webapi\Model\Soap\Server\Factory $soapServerFactory,
        \Magento\Webapi\Model\Config\ClassReflector\TypeProcessor $typeProcessor,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        if (!extension_loaded('soap')) {
            throw new \Magento\Webapi\Exception(
                'SOAP extension is not loaded.',
                0,
                \Magento\Webapi\Exception::HTTP_INTERNAL_ERROR
            );
        }
        $this->_areaList = $areaList;
        $this->_configScope = $configScope;
        $this->_request = $request;
        $this->_domDocumentFactory = $domDocumentFactory;
        $this->_storeManager = $storeManager;
        $this->_soapServerFactory = $soapServerFactory;
        $this->_typeProcessor = $typeProcessor;
        $this->_scopeConfig = $scopeConfig;
        /** Enable or disable SOAP extension WSDL cache depending on Magento configuration. */
        $wsdlCacheEnabled = $this->_scopeConfig->isSetFlag(
            self::CONFIG_PATH_WSDL_CACHE_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($wsdlCacheEnabled) {
            ini_set('soap.wsdl_cache_enabled', '1');
        } else {
            ini_set('soap.wsdl_cache_enabled', '0');
        }
    }

    /**
     * Handle SOAP request. Response is sent by SOAP server.
     *
     * @return void
     */
    public function handle()
    {
        $rawRequestBody = file_get_contents('php://input');
        $this->_checkRequest($rawRequestBody);
        $options = array('encoding' => $this->getApiCharset(), 'soap_version' => SOAP_1_2);
        $soapServer = $this->_soapServerFactory->create($this->generateUri(true), $options);
        $soapServer->handle($rawRequestBody);
    }

    /**
     * Retrieve charset used in SOAP API.
     *
     * @return string
     */
    public function getApiCharset()
    {
        $charset = $this->_scopeConfig->getValue(
            self::CONFIG_PATH_SOAP_CHARSET,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $charset ? $charset : self::SOAP_DEFAULT_ENCODING;
    }

    /**
     * Get SOAP endpoint URL.
     *
     * @param bool $isWsdl
     * @return string
     */
    public function generateUri($isWsdl = false)
    {
        $params = array(self::REQUEST_PARAM_SERVICES => $this->_request->getParam(self::REQUEST_PARAM_SERVICES));
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
        return $this->_storeManager->getStore()->getBaseUrl()
            . $this->_areaList->getFrontName($this->_configScope->getCurrentScope())
            . '/' . $this->_storeManager->getStore()->getCode();
    }

    /**
     * Generate exception if request is invalid.
     *
     * @param string $soapRequest
     * @throws \Magento\Webapi\Exception With invalid SOAP extension
     * @return $this
     */
    protected function _checkRequest($soapRequest)
    {
        $dom = new \DOMDocument();
        if (strlen($soapRequest) == 0 || !$dom->loadXML($soapRequest)) {
            throw new \Magento\Webapi\Exception(__('Invalid XML'), 0, \Magento\Webapi\Exception::HTTP_INTERNAL_ERROR);
        }
        foreach ($dom->childNodes as $child) {
            if ($child->nodeType === XML_DOCUMENT_TYPE_NODE) {
                throw new \Magento\Webapi\Exception(
                    __('Invalid XML: Detected use of illegal DOCTYPE'),
                    0,
                    \Magento\Webapi\Exception::HTTP_INTERNAL_ERROR
                );
            }
        }
        return $this;
    }
}
