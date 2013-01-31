<?php
/**
 * Front controller associated with API area.
 *
 * The main responsibility of this class is to identify requested API type and instantiate correct dispatcher for it.
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
class Mage_Webapi_Controller_Front implements Mage_Core_Controller_FrontInterface
{
    /**#@+
     * API types
     */
    const API_TYPE_REST = 'rest';
    const API_TYPE_SOAP = 'soap';
    /**#@-*/

    /**
     * Specific front controller for current API type.
     *
     * @var Mage_Webapi_Controller_DispatcherInterface
     */
    protected $_dispatcher;

    /** @var Mage_Core_Model_App */
    protected $_application;

    /** @var Mage_Webapi_Helper_Data */
    protected $_helper;

    /** @var string */
    protected $_apiType;

    /** @var Mage_Webapi_Controller_Dispatcher_Factory */
    protected $_dispatcherFactory;

    /** @var Magento_Controller_Router_Route_Factory */
    protected $_routeFactory;

    /** @var Mage_Webapi_Controller_Dispatcher_ErrorProcessor */
    protected $_errorProcessor;

    /**
     * Initialize dependencies.
     *
     * @param Mage_Core_Model_Factory_Helper $helperFactory
     * @param Mage_Webapi_Controller_Dispatcher_Factory $dispatcherFactory
     * @param Mage_Core_Model_App $application
     * @param Magento_Controller_Router_Route_Factory $routeFactory
     * @param Mage_Webapi_Controller_Dispatcher_ErrorProcessor $errorProcessor
     */
    public function __construct(
        Mage_Core_Model_Factory_Helper $helperFactory,
        Mage_Webapi_Controller_Dispatcher_Factory $dispatcherFactory,
        Mage_Core_Model_App $application,
        Magento_Controller_Router_Route_Factory $routeFactory,
        Mage_Webapi_Controller_Dispatcher_ErrorProcessor $errorProcessor
    ) {
        $this->_helper = $helperFactory->get('Mage_Webapi_Helper_Data');
        $this->_dispatcherFactory = $dispatcherFactory;
        $this->_application = $application;
        $this->_routeFactory = $routeFactory;
        $this->_errorProcessor = $errorProcessor;
    }

    /**
     * Prepare environment, initialize dispatcher.
     *
     * @return Mage_Webapi_Controller_Front
     */
    public function init()
    {
        ini_set('display_startup_errors', 0);
        ini_set('display_errors', 0);

        return $this;
    }

    /**
     * Dispatch request and send response.
     *
     * @return Mage_Webapi_Controller_Front
     */
    public function dispatch()
    {
        try {
            $this->_getDispatcher()->dispatch();
        } catch (Exception $e) {
            $this->_errorProcessor->renderException($e);
        }
        return $this;
    }

    /**
     * Retrieve front controller for concrete API type (factory method).
     *
     * @return Mage_Webapi_Controller_DispatcherInterface
     * @throws Mage_Core_Exception
     */
    protected function _getDispatcher()
    {
        if ($this->_dispatcher === null) {
            $this->_dispatcher = $this->_dispatcherFactory->get($this->determineApiType());
        }
        return $this->_dispatcher;
    }

    /**
     * Return the list of defined API types.
     *
     * @return array
     */
    public function getListOfAvailableApiTypes()
    {
        return array(
            self::API_TYPE_REST,
            self::API_TYPE_SOAP
        );
    }

    /**
     * Determine current API type using application request (not web API request).
     *
     * @return string
     * @throws Mage_Core_Exception
     * @throws Mage_Webapi_Exception If requested API type is invalid.
     */
    public function determineApiType()
    {
        if ($this->_apiType === null) {
            $request = $this->_application->getRequest();
            $apiRoute = $this->_routeFactory->createRoute(
                'Mage_Webapi_Controller_Router_Route_Webapi',
                Mage_Webapi_Controller_Router_Route_Webapi::getApiRoute()
            );
            if (!($apiTypeMatch = $apiRoute->match($request, true))) {
                throw new Mage_Webapi_Exception($this->_helper->__('Request does not match any API type route.'),
                    Mage_Webapi_Exception::HTTP_BAD_REQUEST);
            }

            $apiType = $apiTypeMatch[Mage_Webapi_Controller_Router_Route_Webapi::PARAM_API_TYPE];
            if (!in_array($apiType, $this->getListOfAvailableApiTypes())) {
                throw new Mage_Webapi_Exception($this->_helper->__('The "%s" API type is not defined.', $apiType),
                    Mage_Webapi_Exception::HTTP_BAD_REQUEST);
            }
            $this->_apiType = $apiType;
        }
        return $this->_apiType;
    }
}
