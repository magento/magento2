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
 * @category    Mage
 * @package     Mage_Api2
 * @copyright  Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * API2 Server
 *
 * @category   Mage
 * @package    Mage_Api2
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Api2_Model_Server
{
    /**
     * Api2 REST type
     */
    const API_TYPE_REST = 'rest';

    /**#@+
     * HTTP Response Codes
     */
    const HTTP_OK                 = 200;
    const HTTP_CREATED            = 201;
    const HTTP_MULTI_STATUS       = 207;
    const HTTP_BAD_REQUEST        = 400;
    const HTTP_UNAUTHORIZED       = 401;
    const HTTP_FORBIDDEN          = 403;
    const HTTP_NOT_FOUND          = 404;
    const HTTP_METHOD_NOT_ALLOWED = 405;
    const HTTP_NOT_ACCEPTABLE     = 406;
    const HTTP_INTERNAL_ERROR     = 500;
    /**#@- */

    /**
     * List of api types
     *
     * @var array
     */
    protected static $_apiTypes = array(self::API_TYPE_REST);

    /**
     * @var Mage_Api2_Model_Auth_User_Abstract
     */
    protected $_authUser;

    /**
     * Run server
     */
    public function run()
    {
        // can not use response object case
        try {
            /** @var $response Mage_Api2_Model_Response */
            $response = Mage::getSingleton('Mage_Api2_Model_Response');
        } catch (Exception $e) {
            Mage::logException($e);

            if (!headers_sent()) {
                header('HTTP/1.1 ' . self::HTTP_INTERNAL_ERROR);
            }
            echo 'Service temporary unavailable';
            return;
        }
        // can not render errors case
        try {
            /** @var $request Mage_Api2_Model_Request */
            $request = Mage::getSingleton('Mage_Api2_Model_Request');
            /** @var $renderer Mage_Api2_Model_Renderer_Interface */
            $renderer = Mage_Api2_Model_Renderer::factory($request->getAcceptTypes());
        } catch (Exception $e) {
            Mage::logException($e);

            $response->setHttpResponseCode(self::HTTP_INTERNAL_ERROR)
                ->setBody('Service temporary unavailable')
                ->sendResponse();
            return;
        }
        // default case
        try {
            /** @var $apiUser Mage_Api2_Model_Auth_User_Abstract */
            $apiUser = $this->_authenticate($request);

            $this->_route($request)
                ->_allow($request, $apiUser)
                ->_dispatch($request, $response, $apiUser);

            if ($response->getHttpResponseCode() == self::HTTP_CREATED) {
                // TODO: Re-factor this after _renderException refactoring
                throw new Mage_Api2_Exception('Resource was partially created', self::HTTP_CREATED);
            }
            //NOTE: At this moment Renderer already could have some content rendered, so we should replace it
            if ($response->isException()) {
                throw new Mage_Api2_Exception('Unhandled simple errors.', self::HTTP_INTERNAL_ERROR);
            }
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_renderException($e, $renderer, $response);
        }

        $response->sendResponse();
    }

    /**
     * Make internal call to api
     *
     * @param Mage_Api2_Model_Request $request
     * @param Mage_Api2_Model_Response $response
     * @return Mage_Api2_Model_Response
     */
    public function internalCall(Mage_Api2_Model_Request $request, Mage_Api2_Model_Response $response)
    {
        $apiUser = $this->_getAuthUser();
        $this->_route($request)
            ->_allow($request, $apiUser)
            ->_dispatch($request, $response, $apiUser);
    }

    /**
     * Authenticate user
     *
     * @throws Exception
     * @param Mage_Api2_Model_Request $request
     * @return Mage_Api2_Model_Auth_User_Abstract
     */
    protected function _authenticate(Mage_Api2_Model_Request $request)
    {
        /** @var $authManager Mage_Api2_Model_Auth */
        $authManager = Mage::getModel('Mage_Api2_Model_Auth');

        $this->_setAuthUser($authManager->authenticate($request));
        return $this->_getAuthUser();
    }

    /**
     * Set auth user
     *
     * @throws Exception
     * @param Mage_Api2_Model_Auth_User_Abstract $authUser
     * @return Mage_Api2_Model_Server
     */
    protected function _setAuthUser(Mage_Api2_Model_Auth_User_Abstract $authUser)
    {
        $this->_authUser = $authUser;
        return $this;
    }

    /**
     * Retrieve existing auth user
     *
     * @throws Exception
     * @return Mage_Api2_Model_Auth_User_Abstract
     */
    protected function _getAuthUser()
    {
        if (!$this->_authUser) {
            throw new Exception("Mage_Api2_Model_Server::internalCall() seems to be executed "
                . "before Mage_Api2_Model_Server::run()");
        }
        return $this->_authUser;
    }

    /**
     * Set all routes of the given api type to Route object
     * Find route that match current URL, set parameters of the route to Request object
     *
     * @param Mage_Api2_Model_Request $request
     * @return Mage_Api2_Model_Server
     */
    protected function _route(Mage_Api2_Model_Request $request)
    {
        /** @var $router Mage_Api2_Model_Router */
        $router = Mage::getModel('Mage_Api2_Model_Router');

        $router->routeApiType($request, true)
            ->setRoutes($this->_getConfig()->getRoutes($request->getApiType()))
            ->route($request);

        return $this;
    }

    /**
     * Global ACL processing
     *
     * @param Mage_Api2_Model_Request $request
     * @param Mage_Api2_Model_Auth_User_Abstract $apiUser
     * @return Mage_Api2_Model_Server
     * @throws Mage_Api2_Exception
     */
    protected function _allow(Mage_Api2_Model_Request $request, Mage_Api2_Model_Auth_User_Abstract $apiUser)
    {
        /** @var $globalAcl Mage_Api2_Model_Acl_Global */
        $globalAcl = Mage::getModel('Mage_Api2_Model_Acl_Global');

        if (!$globalAcl->isAllowed($apiUser, $request->getResourceType(), $request->getOperation())) {
            throw new Mage_Api2_Exception('Access denied', self::HTTP_FORBIDDEN);
        }
        return $this;
    }

    /**
     * Load class file, instantiate resource class, set parameters to the instance, run resource internal dispatch
     * method
     *
     * @param Mage_Api2_Model_Request $request
     * @param Mage_Api2_Model_Response $response
     * @param Mage_Api2_Model_Auth_User_Abstract $apiUser
     * @return Mage_Api2_Model_Server
     */
    protected function _dispatch(
        Mage_Api2_Model_Request $request,
        Mage_Api2_Model_Response $response,
        Mage_Api2_Model_Auth_User_Abstract $apiUser
    )
    {
        /** @var $dispatcher Mage_Api2_Model_Dispatcher */
        $dispatcher = Mage::getModel('Mage_Api2_Model_Dispatcher');
        $dispatcher->setApiUser($apiUser)->dispatch($request, $response);

        return $this;
    }

    /**
     * Get api2 config instance
     *
     * @return Mage_Api2_Model_Config
     */
    protected function _getConfig()
    {
        return Mage::getModel('Mage_Api2_Model_Config');
    }

    /**
     * Process thrown exception
     * Generate and set HTTP response code, error message to Response object
     *
     * @param Exception $exception
     * @param Mage_Api2_Model_Renderer_Interface $renderer
     * @param Mage_Api2_Model_Response $response
     * @return Mage_Api2_Model_Server
     */
    protected function _renderException(Exception $exception, Mage_Api2_Model_Renderer_Interface $renderer,
        Mage_Api2_Model_Response $response
    ) {
        if ($exception instanceof Mage_Api2_Exception && $exception->getCode()) {
            $httpCode = $exception->getCode();
        } else {
            $httpCode = self::HTTP_INTERNAL_ERROR;
        }
        try {
            //add last error to stack
            $response->setException($exception);

            $messages = array();

            /** @var Exception $exception */
            foreach ($response->getException() as $exception) {
                $message = array('code' => $exception->getCode(), 'message' => $exception->getMessage());

                if (Mage::getIsDeveloperMode()) {
                    $message['trace'] = $exception->getTraceAsString();
                }
                $messages['messages']['error'][] = $message;
            }
            //set HTTP Code of last error, Content-Type and Body
            $response->setBody($renderer->render($messages));
            $response->setHeader('Content-Type', sprintf(
                '%s; charset=%s', $renderer->getMimeType(), Mage_Api2_Model_Response::RESPONSE_CHARSET
            ));
        } catch (Exception $e) {
            //tunnelling of 406(Not acceptable) error
            $httpCode = $e->getCode() == self::HTTP_NOT_ACCEPTABLE    //$e->getCode() can result in one more loop
                    ? self::HTTP_NOT_ACCEPTABLE                      // of try..catch
                    : self::HTTP_INTERNAL_ERROR;

            //if error appeared in "error rendering" process then show it in plain text
            $response->setBody($e->getMessage());
            $response->setHeader('Content-Type', 'text/plain; charset=' . Mage_Api2_Model_Response::RESPONSE_CHARSET);
        }
        $response->setHttpResponseCode($httpCode);

        return $this;
    }

    /**
     * Retrieve api types
     *
     * @return array
     */
    public static function getApiTypes()
    {
        return self::$_apiTypes;
    }
}
