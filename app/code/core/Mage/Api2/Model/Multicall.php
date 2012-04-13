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
 * API2 model for multiple internal calls to subresources of specified resource
 *
 * @category   Mage
 * @package    Mage_Api2
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Api2_Model_Multicall
{

    /**
     * @var Mage_Api2_Model_Request
     */
    protected $_parentCallRequest;

    /**
     * @var string
     */
    protected $_parentResourceId;

    /**
     * Multicall to subresources of specified resource
     *
     * @param string $parentResourceId
     * @param string $parentResourceName
     * @param Mage_Api2_Model_Request $parentCallRequest
     * @return Mage_Api2_Model_Response
     */
    public function call($parentResourceId, $parentResourceName, Mage_Api2_Model_Request $parentCallRequest)
    {
        $this->_parentResourceId = $parentResourceId;
        $this->_parentCallRequest = $parentCallRequest;
        $subresources = $this->_getDeclaredSubresources($parentResourceName);
        foreach ($subresources as $subresource) {
            $this->_callSubresource($subresource);
        }

        return $this->_getResponse();
    }

    /**
     * Make call to specified subresource with data from request
     *
     * @param Mage_Core_Model_Config_Element $subresource
     * @return Mage_Api2_Model_Multicall
     */
    protected function _callSubresource($subresource)
    {
        $bodyParams = $this->_getRequest()->getBodyParams();
        // check if subresource data exists in request
        $requestParamName = (string)$subresource->request_param_name;
        if (!(is_array($bodyParams) && array_key_exists($requestParamName, $bodyParams)
            && is_array($bodyParams[$requestParamName]))
        ) {
            return $this;
        }
        // make internal call
        $subresourceType = (string)$subresource->type;
        $requestData = $bodyParams[$requestParamName];
        switch ($subresourceType) {
            case 'collection':
                foreach ($requestData as $subresourceData) {
                    $this->_internalCall($subresource, $subresourceData);
                }
                break;
            case 'instance':
            default:
                $this->_internalCall($subresource, $requestData);
                break;
        }
        return $this;
    }

    /**
     * Make internal call to specified subresource on with specified data via API2 server
     *
     * @param Mage_Core_Model_Config_Element $subresource
     * @param array $requestData
     * @throws Mage_Api2_Exception
     * @return Mage_Api2_Model_Multicall
     */
    protected function _internalCall($subresource, $requestData)
    {
        try {
            if (!is_array($requestData)) {
                throw new Mage_Api2_Exception('Invalid data format', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
            }
            $subresourceIdKey = (string)$subresource->id_param_name;
            /** @var $server Mage_Api2_Model_Server */
            $server = Mage::getSingleton('Mage_Api2_Model_Server');

            // create subresource item before linking it to main resource
            if (!array_key_exists($subresourceIdKey, $requestData)) {
                $subresourceCreateResourceName = (string)$subresource->create_resource_name;
                $internalRequest = $this->_prepareRequest($subresourceCreateResourceName, $requestData);
                /** @var $internalCreateResponse Mage_Api2_Model_Response */
                $internalCreateResponse = Mage::getModel('Mage_Api2_Model_Response');
                $server->internalCall($internalRequest, $internalCreateResponse);
                $createdSubresourceInstanceId = $this->_getCreatedResourceId($internalCreateResponse);
                if (empty($createdSubresourceInstanceId)) {
                    throw new Mage_Api2_Exception('Error during subresource creation',
                        Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
                }
                $requestData[$subresourceIdKey] = $createdSubresourceInstanceId;
            }

            // link subresource to main resource
            $subresourceName = (string)$subresource->name;
            $parentResourceIdFieldName = (string)$subresource->parent_resource_id_field_name;
            $internalRequest = $this->_prepareRequest($subresourceName, $requestData, $parentResourceIdFieldName);

            /** @var $internalResponse Mage_Api2_Model_Response */
            $internalResponse = Mage::getModel('Mage_Api2_Model_Response');
            $server->internalCall($internalRequest, $internalResponse);
        } catch (Exception $e) {
            // TODO: implement strict mode
            Mage::logException($e);
            $this->_getResponse()->setException($e);
            // TODO: Refactor partial success idintification process
            $this->_getResponse()->setHttpResponseCode(Mage_Api2_Model_Server::HTTP_CREATED);
        }

        if (isset($internalCreateResponse)) {
            $this->_aggregateResponse($internalCreateResponse);
        }
        if (isset($internalResponse)) {
            $this->_aggregateResponse($internalResponse);
        }

        return $this;
    }

    /**
     * Prepare internal request
     *
     * @param string $subresourceName
     * @param array $data
     * @param string|null $parentResourceIdFieldName
     * @return Mage_Api2_Model_Request_Internal
     */
    protected function _prepareRequest($subresourceName, $data, $parentResourceIdFieldName = null)
    {
        $subresourceUri = $this->_createSubresourceUri($subresourceName, $parentResourceIdFieldName);
        /** @var $internalRequest Mage_Api2_Model_Request_Internal */
        $internalRequest = Mage::getModel('Mage_Api2_Model_Request_Internal');
        $internalRequest->setRequestUri($subresourceUri);
        $internalRequest->setBodyParams($data);
        $internalRequest->setMethod('POST');
        return $internalRequest;
    }

    /**
     * Generate subresource uri
     *
     * @param string $subresourceName
     * @param string $parentResourceIdFieldName
     * @return string
     */
    protected function _createSubresourceUri($subresourceName, $parentResourceIdFieldName = null)
    {
        /** @var $apiTypeRoute Mage_Api2_Model_Route_ApiType */
        $apiTypeRoute = Mage::getModel('Mage_Api2_Model_Route_ApiType');

        $chain = $apiTypeRoute->chain(
            new Zend_Controller_Router_Route($this->_getConfig()->getMainRoute($subresourceName))
        );
        $params = array();
        $params['api_type'] = 'rest';
        if (null !== $parentResourceIdFieldName) {
            $params[$parentResourceIdFieldName] = $this->_parentResourceId;
        }
        $uri = $chain->assemble($params);

        return '/' . $uri;
    }

    /**
     * Retrieve list of subresources declared in configuration
     *
     * @param string $parentResourceName
     * @return array
     */
    protected function _getDeclaredSubresources($parentResourceName)
    {
        return $this->_getConfig()->getResourceSubresources($parentResourceName);
    }

    /**
     * Retrieve API2 config
     *
     * @return Mage_Api2_Model_Config
     */
    protected function _getConfig()
    {
        return Mage::getSingleton('Mage_Api2_Model_Config');
    }

    /**
     * Retrieve global response
     *
     * @return Mage_Api2_Model_Response
     */
    protected function _getResponse()
    {
        return Mage::getSingleton('Mage_Api2_Model_Response');
    }

    /**
     * Retrieve parent request
     *
     * @return Mage_Api2_Model_Request
     */
    protected function _getRequest()
    {
        return $this->_parentCallRequest;
    }

    /**
     * Add internal call response to global response
     *
     * @param Mage_Api2_Model_Response $response
     */
    protected function _aggregateResponse(Mage_Api2_Model_Response $response)
    {
        if ($response->isException()) {
            $errors = $response->getException();
            // @TODO: add subresource prefix to error messages
            foreach ($errors as $error) {
                $this->_getResponse()->setException($error);
            }
        }
    }

    /**
     * Retrieve created resource id from response
     *
     * @param Mage_Api2_Model_Response $response
     * @return string|int
     */
    protected function _getCreatedResourceId($response)
    {
        $resourceId = 0;
        $headers = $response->getHeaders();
        foreach ($headers as $header) {
            if ($header['name'] == 'Location') {
                list($resourceId) = array_reverse(explode('/', $header['value']));
                break;
            }
        }
        return $resourceId;
    }
}
