<?php
/**
 * Front controller for WebAPI SOAP area.
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
namespace Magento\Webapi\Controller;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Soap implements \Magento\App\FrontControllerInterface
{
    /**#@+
     * Content types used for responses processed by SOAP web API.
     */
    const CONTENT_TYPE_SOAP_CALL = 'application/soap+xml';
    const CONTENT_TYPE_WSDL_REQUEST = 'text/xml';
    /**#@-*/

    /** @var \Magento\Webapi\Model\Soap\Server */
    protected $_soapServer;

    /** @var \Magento\Webapi\Model\Soap\Wsdl\Generator */
    protected $_wsdlGenerator;

    /** @var \Magento\Webapi\Controller\Soap\Request */
    protected $_request;

    /** @var \Magento\Webapi\Controller\Response */
    protected $_response;

    /** @var \Magento\Webapi\Controller\ErrorProcessor */
    protected $_errorProcessor;

    /** @var \Magento\App\State */
    protected $_appState;

    /** @var \Magento\Core\Model\App */
    protected $_application;

    /** @var \Magento\Oauth\Service\OauthV1Interface */
    protected $_oauthService;

    /** @var  \Magento\Oauth\Helper\Service */
    protected $_oauthHelper;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Webapi\Controller\Soap\Request $request
     * @param \Magento\Webapi\Controller\Response $response
     * @param \Magento\Webapi\Model\Soap\Wsdl\Generator $wsdlGenerator
     * @param \Magento\Webapi\Model\Soap\Server $soapServer
     * @param \Magento\Webapi\Controller\ErrorProcessor $errorProcessor
     * @param \Magento\App\State $appState
     * @param \Magento\Core\Model\App $application
     * @param \Magento\Oauth\Service\OauthV1Interface $oauthService
     * @param \Magento\Oauth\Helper\Service $oauthHelper
     */
    public function __construct(
        \Magento\Webapi\Controller\Soap\Request $request,
        \Magento\Webapi\Controller\Response $response,
        \Magento\Webapi\Model\Soap\Wsdl\Generator $wsdlGenerator,
        \Magento\Webapi\Model\Soap\Server $soapServer,
        \Magento\Webapi\Controller\ErrorProcessor $errorProcessor,
        \Magento\App\State $appState,
        \Magento\Core\Model\App $application,
        \Magento\Oauth\Service\OauthV1Interface $oauthService,
        \Magento\Oauth\Helper\Service $oauthHelper
    ) {
        $this->_request = $request;
        $this->_response = $response;
        $this->_wsdlGenerator = $wsdlGenerator;
        $this->_soapServer = $soapServer;
        $this->_errorProcessor = $errorProcessor;
        $this->_appState = $appState;
        $this->_application = $application;
        $this->_oauthService = $oauthService;
        $this->_oauthHelper = $oauthHelper;
    }

    /**
     * Initialize front controller
     *
     * @return \Magento\Webapi\Controller\Soap
     */
    public function init()
    {
        return $this;
    }

    /**
     * @param \Magento\App\RequestInterface $request
     * @return $this
     */
    public function dispatch(\Magento\App\RequestInterface $request)
    {
        $pathParts = explode('/', trim($request->getPathInfo(), '/'));
        array_shift($pathParts);
        $request->setPathInfo('/' . implode('/', $pathParts));
        try {
            if (!$this->_appState->isInstalled()) {
                throw new \Magento\Webapi\Exception(__('Magento is not yet installed'));
            }
            if ($this->_isWsdlRequest()) {
                $responseBody = $this->_wsdlGenerator->generate(
                    $this->_request->getRequestedServices(),
                    $this->_soapServer->generateUri()
                );
                $this->_setResponseContentType(self::CONTENT_TYPE_WSDL_REQUEST);
            } else {
                $this->_oauthService->validateAccessToken(array('token' => $this->_getAccessToken()));
                $responseBody = $this->_soapServer->handle();
                $this->_setResponseContentType(self::CONTENT_TYPE_SOAP_CALL);
            }
            $this->_setResponseBody($responseBody);
        } catch (\Exception $e) {
            $this->_prepareErrorResponse($e);
        }
        $this->_response->sendResponse();
        return $this;
    }

    /**
     * Check if current request is WSDL request. SOAP operation execution request is another type of requests.
     *
     * @return bool
     */
    protected function _isWsdlRequest()
    {
        return $this->_request->getParam(\Magento\Webapi\Model\Soap\Server::REQUEST_PARAM_WSDL) !== null;
    }

    /**
     * Parse the Authorization header and return the access token
     * eg Authorization: Bearer <access-token>
     *
     * @return string Access token
     */
    protected function _getAccessToken()
    {
        $token = explode(' ', $_SERVER['HTTP_AUTHORIZATION']);
        return $token[1];
    }

    /**
     * Set body and status code to response using information extracted from provided exception.
     *
     * @param \Exception $exception
     */
    protected function _prepareErrorResponse($exception)
    {
        $maskedException = $this->_errorProcessor->maskException($exception);
        $soapFault = new \Magento\Webapi\Model\Soap\Fault($this->_application, $maskedException);
        if ($this->_isWsdlRequest()) {
            $httpCode = $maskedException->getHttpCode();
            $contentType = self::CONTENT_TYPE_WSDL_REQUEST;
        } else {
            $httpCode = \Magento\Webapi\Controller\Response::HTTP_OK;
            $contentType = self::CONTENT_TYPE_SOAP_CALL;
        }
        $this->_setResponseContentType($contentType);
        $this->_response->setHttpResponseCode($httpCode);
        // TODO: Generate list of available URLs when invalid WSDL URL specified
        $this->_setResponseBody($soapFault->toXml());
    }

    /**
     * Set content type to response object.
     *
     * @param string $contentType
     * @return \Magento\Webapi\Controller\Soap
     */
    protected function _setResponseContentType($contentType = 'text/xml')
    {
        $this->_response->clearHeaders()
            ->setHeader('Content-Type', "$contentType; charset={$this->_soapServer->getApiCharset()}");
        return $this;
    }

    /**
     * Replace WSDL xml encoding from config, if present, else default to UTF-8 and set it to the response object.
     *
     * @param string $responseBody
     * @return \Magento\Webapi\Controller\Soap
     */
    protected function _setResponseBody($responseBody)
    {
        $this->_response->setBody(
            preg_replace(
                '/<\?xml version="([^\"]+)"([^\>]+)>/i',
                '<?xml version="$1" encoding="' . $this->_soapServer->getApiCharset() . '"?>',
                $responseBody
            )
        );
        return $this;
    }
}
