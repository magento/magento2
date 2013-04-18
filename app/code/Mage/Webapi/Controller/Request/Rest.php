<?php
/**
 * REST API request.
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
class Mage_Webapi_Controller_Request_Rest extends Mage_Webapi_Controller_Request
{
    /**
     * Character set which must be used in request.
     */
    const REQUEST_CHARSET = 'utf-8';

    /**#@+
     * HTTP methods supported by REST.
     */
    const HTTP_METHOD_CREATE = 'create';
    const HTTP_METHOD_GET = 'get';
    const HTTP_METHOD_UPDATE = 'update';
    const HTTP_METHOD_DELETE = 'delete';
    /**#@-*/

    /**#@+
     * Resource types.
     */
    const ACTION_TYPE_ITEM = 'item';
    const ACTION_TYPE_COLLECTION = 'collection';
    /**#@-*/

    /** @var string */
    protected $_resourceName;

    /** @var string */
    protected $_resourceType;

    /** @var string */
    protected $_resourceVersion;

    /**
     * @var Mage_Webapi_Controller_Request_Rest_InterpreterInterface
     */
    protected $_interpreter;

    /** @var array */
    protected $_bodyParams;

    /** @var Mage_Webapi_Helper_Data */
    protected $_helper;

    /** @var Mage_Webapi_Controller_Request_Rest_Interpreter_Factory */
    protected $_interpreterFactory;

    /**
     * Initialize dependencies.
     *
     * @param Mage_Webapi_Controller_Request_Rest_Interpreter_Factory $interpreterFactory
     * @param Mage_Webapi_Helper_Data $helper
     * @param string|null $uri
     */
    public function __construct(
        Mage_Webapi_Controller_Request_Rest_Interpreter_Factory $interpreterFactory,
        Mage_Webapi_Helper_Data $helper,
        $uri = null
    ) {
        parent::__construct(Mage_Webapi_Controller_Front::API_TYPE_REST, $uri);
        $this->_helper = $helper;
        $this->_interpreterFactory = $interpreterFactory;
    }

    /**
     * Get request interpreter.
     *
     * @return Mage_Webapi_Controller_Request_Rest_InterpreterInterface
     */
    protected function _getInterpreter()
    {
        if (null === $this->_interpreter) {
            $this->_interpreter = $this->_interpreterFactory->get($this->getContentType());
        }
        return $this->_interpreter;
    }

    /**
     * Retrieve accept types understandable by requester in a form of array sorted by quality in descending order.
     *
     * @return array
     */
    public function getAcceptTypes()
    {
        $qualityToTypes = array();
        $orderedTypes = array();

        foreach (preg_split('/,\s*/', $this->getHeader('Accept')) as $definition) {
            $typeWithQ = explode(';', $definition);
            $mimeType = trim(array_shift($typeWithQ));

            // check MIME type validity
            if (!preg_match('~^([0-9a-z*+\-]+)(?:/([0-9a-z*+\-\.]+))?$~i', $mimeType)) {
                continue;
            }
            $quality = '1.0'; // default value for quality

            if ($typeWithQ) {
                $qAndValue = explode('=', $typeWithQ[0]);

                if (2 == count($qAndValue)) {
                    $quality = $qAndValue[1];
                }
            }
            $qualityToTypes[$quality][$mimeType] = true;
        }
        krsort($qualityToTypes);

        foreach ($qualityToTypes as $typeList) {
            $orderedTypes += $typeList;
        }
        return array_keys($orderedTypes);
    }

    /**
     * Fetch data from HTTP Request body.
     *
     * @return array
     */
    public function getBodyParams()
    {
        if (null == $this->_bodyParams) {
            $this->_bodyParams = $this->_getInterpreter()->interpret((string)$this->getRawBody());
        }
        return $this->_bodyParams;
    }

    /**
     * Get Content-Type of request.
     *
     * @return string
     * @throws Mage_Webapi_Exception
     */
    public function getContentType()
    {
        $headerValue = $this->getHeader('Content-Type');

        if (!$headerValue) {
            throw new Mage_Webapi_Exception($this->_helper->__('Content-Type header is empty.'),
                Mage_Webapi_Exception::HTTP_BAD_REQUEST);
        }
        if (!preg_match('~^([a-z\d/\-+.]+)(?:; *charset=(.+))?$~Ui', $headerValue, $matches)) {
            throw new Mage_Webapi_Exception($this->_helper->__('Content-Type header is invalid.'),
                Mage_Webapi_Exception::HTTP_BAD_REQUEST);
        }
        // request encoding check if it is specified in header
        if (isset($matches[2]) && self::REQUEST_CHARSET != strtolower($matches[2])) {
            throw new Mage_Webapi_Exception($this->_helper->__('UTF-8 is the only supported charset.'),
                Mage_Webapi_Exception::HTTP_BAD_REQUEST);
        }

        return $matches[1];
    }

    /**
     * Retrieve one of CRUD operations depending on HTTP method.
     *
     * @return string
     * @throws Mage_Webapi_Exception
     */
    public function getHttpMethod()
    {
        if (!$this->isGet() && !$this->isPost() && !$this->isPut() && !$this->isDelete()) {
            throw new Mage_Webapi_Exception($this->_helper->__('Request method is invalid.'),
                Mage_Webapi_Exception::HTTP_BAD_REQUEST);
        }
        // Map HTTP methods to classic CRUD verbs
        $operationByMethod = array(
            'GET' => self::HTTP_METHOD_GET,
            'POST' => self::HTTP_METHOD_CREATE,
            'PUT' => self::HTTP_METHOD_UPDATE,
            'DELETE' => self::HTTP_METHOD_DELETE
        );

        return $operationByMethod[$this->getMethod()];
    }

    /**
     * Retrieve resource type.
     *
     * @return string
     */
    public function getResourceName()
    {
        return $this->_resourceName;
    }

    /**
     * Set resource type.
     *
     * @param string $resourceName
     */
    public function setResourceName($resourceName)
    {
        $this->_resourceName = $resourceName;
    }

    /**
     * Retrieve action type.
     *
     * @return string|null
     */
    public function getResourceType()
    {
        return $this->_resourceType;
    }

    /**
     * Set resource type.
     *
     * @param string $resourceType
     */
    public function setResourceType($resourceType)
    {
        $this->_resourceType = $resourceType;
    }

    /**
     * Retrieve action version.
     *
     * @return int
     * @throws LogicException If resource version cannot be identified.
     */
    public function getResourceVersion()
    {
        if (!$this->_resourceVersion) {
            $this->setResourceVersion($this->getParam(Mage_Webapi_Controller_Router_Route_Rest::PARAM_VERSION));
        }
        return $this->_resourceVersion;
    }

    /**
     * Set resource version.
     *
     * @param string|int $resourceVersion Version number either with prefix or without it
     * @throws Mage_Webapi_Exception
     * @return Mage_Webapi_Controller_Request_Rest
     */
    public function setResourceVersion($resourceVersion)
    {
        $versionPrefix = Mage_Webapi_Model_ConfigAbstract::VERSION_NUMBER_PREFIX;
        if (preg_match("/^{$versionPrefix}?(\d+)$/i", $resourceVersion, $matches)) {
            $versionNumber = (int)$matches[1];
        } else {
            throw new Mage_Webapi_Exception(
                $this->_helper->__("Resource version is not specified or invalid one is specified."),
                Mage_Webapi_Exception::HTTP_BAD_REQUEST
            );
        }
        $this->_resourceVersion = $versionNumber;
        return $this;
    }

    /**
     * Identify operation name according to HTTP request parameters.
     *
     * @return string
     * @throws Mage_Webapi_Exception
     */
    public function getOperationName()
    {
        $restMethodsMap = array(
            self::ACTION_TYPE_COLLECTION . self::HTTP_METHOD_CREATE =>
                Mage_Webapi_Controller_ActionAbstract::METHOD_CREATE,
            self::ACTION_TYPE_COLLECTION . self::HTTP_METHOD_GET =>
                Mage_Webapi_Controller_ActionAbstract::METHOD_LIST,
            self::ACTION_TYPE_COLLECTION . self::HTTP_METHOD_UPDATE =>
                Mage_Webapi_Controller_ActionAbstract::METHOD_MULTI_UPDATE,
            self::ACTION_TYPE_COLLECTION . self::HTTP_METHOD_DELETE =>
                Mage_Webapi_Controller_ActionAbstract::METHOD_MULTI_DELETE,
            self::ACTION_TYPE_ITEM . self::HTTP_METHOD_GET => Mage_Webapi_Controller_ActionAbstract::METHOD_GET,
            self::ACTION_TYPE_ITEM . self::HTTP_METHOD_UPDATE => Mage_Webapi_Controller_ActionAbstract::METHOD_UPDATE,
            self::ACTION_TYPE_ITEM . self::HTTP_METHOD_DELETE => Mage_Webapi_Controller_ActionAbstract::METHOD_DELETE,
        );
        $httpMethod = $this->getHttpMethod();
        $resourceType = $this->getResourceType();
        if (!isset($restMethodsMap[$resourceType . $httpMethod])) {
            throw new Mage_Webapi_Exception($this->_helper->__('Requested method does not exist.'),
                Mage_Webapi_Exception::HTTP_NOT_FOUND);
        }
        $methodName = $restMethodsMap[$resourceType . $httpMethod];
        if ($methodName == self::HTTP_METHOD_CREATE) {
            /** If request is numeric array, multi create operation must be used. */
            $params = $this->getBodyParams();
            if (count($params)) {
                $keys = array_keys($params);
                if (is_numeric($keys[0])) {
                    $methodName = Mage_Webapi_Controller_ActionAbstract::METHOD_MULTI_CREATE;
                }
            }
        }
        $operationName = $this->getResourceName() . ucfirst($methodName);
        return $operationName;
    }

    /**
     * Identify resource type by operation name.
     *
     * @param string $operation
     * @return string 'collection' or 'item'
     * @throws InvalidArgumentException When method does not match the list of allowed methods
     */
    public static function getActionTypeByOperation($operation)
    {
        $actionTypeMap = array(
            Mage_Webapi_Controller_ActionAbstract::METHOD_CREATE => self::ACTION_TYPE_COLLECTION,
            Mage_Webapi_Controller_ActionAbstract::METHOD_MULTI_CREATE => self::ACTION_TYPE_COLLECTION,
            Mage_Webapi_Controller_ActionAbstract::METHOD_GET => self::ACTION_TYPE_ITEM,
            Mage_Webapi_Controller_ActionAbstract::METHOD_LIST => self::ACTION_TYPE_COLLECTION,
            Mage_Webapi_Controller_ActionAbstract::METHOD_UPDATE => self::ACTION_TYPE_ITEM,
            Mage_Webapi_Controller_ActionAbstract::METHOD_MULTI_UPDATE => self::ACTION_TYPE_COLLECTION,
            Mage_Webapi_Controller_ActionAbstract::METHOD_DELETE => self::ACTION_TYPE_ITEM,
            Mage_Webapi_Controller_ActionAbstract::METHOD_MULTI_DELETE => self::ACTION_TYPE_COLLECTION,
        );
        if (!isset($actionTypeMap[$operation])) {
            throw new InvalidArgumentException(sprintf('The "%s" method is not a valid resource method.', $operation));
        }
        return $actionTypeMap[$operation];
    }
}
