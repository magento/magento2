<?php
/**
 * Helper for data processing according to REST presentation.
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
class Mage_Webapi_Controller_Dispatcher_Rest_Presentation
{
    /** @var Mage_Webapi_Controller_Dispatcher_Rest_Presentation_Request */
    protected $_requestProcessor;

    /** @var Mage_Webapi_Controller_Dispatcher_Rest_Presentation_Response */
    protected $_responseProcessor;

    /**
     * Initialize dependencies.
     *
     * @param Mage_Webapi_Controller_Dispatcher_Rest_Presentation_Request $requestPresentation
     * @param Mage_Webapi_Controller_Dispatcher_Rest_Presentation_Response $responsePresentation
     */
    public function __construct(
        Mage_Webapi_Controller_Dispatcher_Rest_Presentation_Request $requestPresentation,
        Mage_Webapi_Controller_Dispatcher_Rest_Presentation_Response $responsePresentation
    ) {
        $this->_requestProcessor = $requestPresentation;
        $this->_responseProcessor = $responsePresentation;
    }

    /**
     * Fetch data from request and prepare it for passing to specified action.
     *
     * @param object $controllerInstance
     * @param string $action
     * @return array
     */
    public function fetchRequestData($controllerInstance, $action)
    {
        return $this->_requestProcessor->fetchRequestData($controllerInstance, $action);
    }

    /**
     * Perform rendering of action results.
     *
     * @param string $method
     * @param array|null $outputData
     */
    public function prepareResponse($method, $outputData = null)
    {
        $this->_responseProcessor->prepareResponse($method, $outputData);
    }
}
