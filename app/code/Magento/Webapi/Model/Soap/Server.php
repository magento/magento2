<?php
/**
 * Magento-specific SOAP server.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Model\Soap;

use Magento\Framework\Webapi\Request;

/**
 * SOAP Server
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Server
{
    const SOAP_DEFAULT_ENCODING = 'UTF-8';

    /**#@+
     * Path in config to Webapi settings.
     */
    const CONFIG_PATH_SOAP_CHARSET = 'webapi/soap/charset';
    /**#@-*/

    const REQUEST_PARAM_SERVICES = 'services';

    const REQUEST_PARAM_WSDL = 'wsdl';

    const REQUEST_PARAM_LIST_WSDL = 'wsdl_list';

    /**
     * @var \Magento\Framework\App\AreaLIst
     */
    protected $_areaList;

    /**
     * @var \Magento\Framework\Config\ScopeInterface
     */
    protected $_configScope;

    /**
     * @var Request
     */
    protected $_request;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Webapi\Model\Soap\ServerFactory
     */
    protected $_soapServerFactory;

    /**
     * @var \Magento\Framework\Reflection\TypeProcessor
     */
    protected $_typeProcessor;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var Wsdl\Generator
     * @since 2.1.0
     */
    private $wsdlGenerator;

    /**
     * Initialize dependencies, initialize WSDL cache.
     *
     * @param \Magento\Framework\App\AreaList $areaList
     * @param \Magento\Framework\Config\ScopeInterface $configScope
     * @param Request $request
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Webapi\Model\Soap\ServerFactory $soapServerFactory
     * @param \Magento\Framework\Reflection\TypeProcessor $typeProcessor
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Wsdl\Generator $wsdlGenerator
     * @throws \Magento\Framework\Webapi\Exception
     */
    public function __construct(
        \Magento\Framework\App\AreaList $areaList,
        \Magento\Framework\Config\ScopeInterface $configScope,
        Request $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Webapi\Model\Soap\ServerFactory $soapServerFactory,
        \Magento\Framework\Reflection\TypeProcessor $typeProcessor,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Webapi\Model\Soap\Wsdl\Generator $wsdlGenerator
    ) {
        if (!extension_loaded('soap')) {
            throw new \Magento\Framework\Webapi\Exception(
                __('SOAP extension is not loaded.'),
                0,
                \Magento\Framework\Webapi\Exception::HTTP_INTERNAL_ERROR
            );
        }
        $this->_areaList = $areaList;
        $this->_configScope = $configScope;
        $this->_request = $request;
        $this->_storeManager = $storeManager;
        $this->_soapServerFactory = $soapServerFactory;
        $this->_typeProcessor = $typeProcessor;
        $this->_scopeConfig = $scopeConfig;
        $this->wsdlGenerator = $wsdlGenerator;
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
        $options = ['encoding' => $this->getApiCharset(), 'soap_version' => SOAP_1_2];
        $soapServer = $this->_soapServerFactory->create($this->getWsdlLocalUri(), $options);
        $soapServer->handle($rawRequestBody);
    }

    /**
     * Get WSDL local URI
     *
     * Local WSDL URI is used to be able to pass wsdl schema to SoapServer without authorization
     *
     * @return string
     * @since 2.1.0
     */
    private function getWsdlLocalUri()
    {
        $wsdlBody = $this->wsdlGenerator->generate(
            $this->_request->getRequestedServices(),
            $this->_request->getScheme(),
            $this->_request->getHttpHost(),
            $this->generateUri()
        );
        return 'data://text/plain;base64,'.base64_encode($wsdlBody);
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
        $params = [self::REQUEST_PARAM_SERVICES => $this->_request->getParam(self::REQUEST_PARAM_SERVICES)];
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
        $storeCode = $this->_storeManager->getStore()->getCode() === \Magento\Store\Model\Store::ADMIN_CODE
            ? \Magento\Webapi\Controller\PathProcessor::ALL_STORE_CODE
            : $this->_storeManager->getStore()->getCode();
        return $this->_storeManager->getStore()->getBaseUrl()
            . $this->_areaList->getFrontName($this->_configScope->getCurrentScope())
            . '/' . $storeCode;
    }

    /**
     * Generate exception if request is invalid.
     *
     * @param string $soapRequest
     * @throws \Magento\Framework\Webapi\Exception With invalid SOAP extension
     * @return $this
     */
    protected function _checkRequest($soapRequest)
    {
        $dom = new \DOMDocument();
        if (strlen($soapRequest) == 0 || !$dom->loadXML($soapRequest)) {
            throw new \Magento\Framework\Webapi\Exception(
                __('Invalid XML'),
                0,
                \Magento\Framework\Webapi\Exception::HTTP_INTERNAL_ERROR
            );
        }
        foreach ($dom->childNodes as $child) {
            if ($child->nodeType === XML_DOCUMENT_TYPE_NODE) {
                throw new \Magento\Framework\Webapi\Exception(
                    __('Invalid XML: Detected use of illegal DOCTYPE'),
                    0,
                    \Magento\Framework\Webapi\Exception::HTTP_INTERNAL_ERROR
                );
            }
        }
        return $this;
    }
}
