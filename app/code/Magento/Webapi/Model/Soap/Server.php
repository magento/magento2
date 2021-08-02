<?php
/**
 * Magento-specific SOAP server.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Model\Soap;

use DOMDocument;
use Magento\Framework\App\AreaList;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\TypeProcessor;
use Magento\Framework\Webapi\Exception;
use Magento\Framework\Webapi\Request;
use Magento\Store\Model\ScopeInterface as ModelScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Webapi\Controller\PathProcessor;
use Magento\Webapi\Model\Soap\Wsdl\Generator;
use Magento\Framework\Config\ScopeInterface;

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
     * @var AreaList
     */
    protected $_areaList;

    /**
     * @var ScopeInterface
     */
    protected $_configScope;

    /**
     * @var Request
     */
    protected $_request;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var ServerFactory
     */
    protected $_soapServerFactory;

    /**
     * @var TypeProcessor
     */
    protected $_typeProcessor;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var Generator
     */
    private $wsdlGenerator;

    /**
     * SOAP version to use; SOAP_1_2 by default, to allow processing of headers.
     *
     * @var int
     */
    private $soapVersion = SOAP_1_2;

    /**
     * Initialize dependencies, initialize WSDL cache.
     *
     * @param AreaList $areaList
     * @param ScopeInterface $configScope
     * @param Request $request
     * @param StoreManagerInterface $storeManager
     * @param ServerFactory $soapServerFactory
     * @param TypeProcessor $typeProcessor
     * @param ScopeConfigInterface $scopeConfig
     * @param Generator $wsdlGenerator
     * @throws Exception
     */
    public function __construct(
        AreaList $areaList,
        ScopeInterface $configScope,
        Request $request,
        StoreManagerInterface $storeManager,
        ServerFactory $soapServerFactory,
        TypeProcessor $typeProcessor,
        ScopeConfigInterface $scopeConfig,
        Generator $wsdlGenerator
    ) {
        if (!extension_loaded('soap')) {
            throw new Exception(__('SOAP extension is not loaded.'), 0, Exception::HTTP_INTERNAL_ERROR);
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
     * @throws NoSuchEntityException|Exception
     */
    public function handle(): void
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
     * @throws NoSuchEntityException|Exception
     */
    private function getWsdlLocalUri(): string
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
    public function getApiCharset(): string
    {
        $charset = $this->_scopeConfig->getValue(
            self::CONFIG_PATH_SOAP_CHARSET,
            ModelScopeInterface::SCOPE_STORE
        );

        return $charset ? $charset : self::SOAP_DEFAULT_ENCODING;
    }

    /**
     * Get SOAP endpoint URL.
     *
     * @param bool $isWsdl
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function generateUri(bool $isWsdl = false): string
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
     * @throws NoSuchEntityException
     */
    public function getEndpointUri(): string
    {
        $storeCode = $this->_storeManager->getStore()->getCode() === Store::ADMIN_CODE
            ? PathProcessor::ALL_STORE_CODE
            : $this->_storeManager->getStore()->getCode();

        return $this->_storeManager->getStore()->getBaseUrl()
            . $this->_areaList->getFrontName($this->_configScope->getCurrentScope())
            . '/' . $storeCode;
    }

    /**
     * Get SOAP version
     *
     * @return int
     */
    public function getSoapVersion(): int
    {
        return $this->soapVersion;
    }

    /**
     * Generate exception if request is invalid.
     *
     * @param string $soapRequest
     *
     * @return Server
     * @throws Exception
     */
    protected function _checkRequest(string $soapRequest): Server
    {
        $dom = new DOMDocument();

        if (strlen($soapRequest) == 0 || !$dom->loadXML($soapRequest)) {
            throw new Exception(
                __('Invalid XML'),
                0,
                Exception::HTTP_INTERNAL_ERROR
            );
        }

        foreach ($dom->childNodes as $child) {
            if ($child->nodeType === XML_DOCUMENT_TYPE_NODE) {
                throw new Exception(
                    __('Invalid XML: Detected use of illegal DOCTYPE'),
                    0,
                    Exception::HTTP_INTERNAL_ERROR
                );
            }
        }

        return $this;
    }
}
