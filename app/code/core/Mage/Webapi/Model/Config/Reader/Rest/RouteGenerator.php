<?php
use Zend\Server\Reflection\ReflectionMethod;

/**
 * REST routes generator.
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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webapi_Model_Config_Reader_Rest_RouteGenerator
{
    /** @var array */
    protected $_routes = array();

    /**
     * @var Mage_Webapi_Helper_Config
     */
    protected $_helper;

    /**
     * Construct routes generator.
     *
     * @param Mage_Webapi_Helper_Config $helper
     */
    public function __construct(Mage_Webapi_Helper_Config $helper)
    {
        $this->_helper = $helper;
    }

    /**
     * Generate a list of routes available fo the specified method.
     *
     * @param ReflectionMethod $methodReflection
     * @return array
     */
    public function generateRestRoutes(ReflectionMethod $methodReflection)
    {
        $routes = array();
        $routePath = "/:" . Mage_Webapi_Controller_Router_Route_Rest::PARAM_VERSION;
        $routeParts = $this->_helper->getResourceNameParts($methodReflection->getDeclaringClass()->getName());
        $partsCount = count($routeParts);
        for ($i = 0; $i < $partsCount; $i++) {
            if ($this->_isParentResourceIdExpected($methodReflection)
                /**
                 * In case of subresource route, parent ID must be specified before the last route part.
                 * E.g.: /v1/grandParent/parent/:parentId/resource
                 */
                && ($i == ($partsCount - 1))
            ) {
                $routePath .= "/:" . Mage_Webapi_Controller_Router_Route_Rest::PARAM_PARENT_ID;
            }
            $routePath .= "/" . lcfirst($this->_helper->convertSingularToPlural($routeParts[$i]));
        }
        if ($this->_isResourceIdExpected($methodReflection)) {
            $routePath .= "/:" . Mage_Webapi_Controller_Router_Route_Rest::PARAM_ID;
        }

        foreach ($this->_getAdditionalRequiredParamNames($methodReflection) as $additionalRequired) {
            $routePath .= "/$additionalRequired/:$additionalRequired";
        }

        $actionType = Mage_Webapi_Controller_Request_Rest::getActionTypeByOperation(
            $this->_helper->getMethodNameWithoutVersionSuffix($methodReflection)
        );
        $resourceName = $this->_helper->translateResourceName($methodReflection->getDeclaringClass()->getName());
        $optionalParams = $this->_getOptionalParamNames($methodReflection);
        foreach ($this->_getPathCombinations($optionalParams, $routePath) as $finalRoutePath) {
            $routes[$finalRoutePath] = array('actionType' => $actionType, 'resourceName' => $resourceName);
        }

        $this->_routes = array_merge($this->_routes, $routes);
        return $routes;
    }

    /**
     * Retrieve all generated routes.
     *
     * @return array
     */
    public function getRoutes()
    {
        return $this->_routes;
    }

    /**
     * Identify if method expects Parent resource ID to be present in the request.
     *
     * @param Zend\Server\Reflection\ReflectionMethod $methodReflection
     * @return bool
     */
    protected function _isParentResourceIdExpected(ReflectionMethod $methodReflection)
    {
        $isIdFieldExpected = false;
        if ($this->_helper->isSubresource($methodReflection)) {
            $methodsWithParentId = array(
                Mage_Webapi_Controller_ActionAbstract::METHOD_CREATE,
                Mage_Webapi_Controller_ActionAbstract::METHOD_LIST,
                Mage_Webapi_Controller_ActionAbstract::METHOD_MULTI_UPDATE,
                Mage_Webapi_Controller_ActionAbstract::METHOD_MULTI_DELETE,
                Mage_Webapi_Controller_ActionAbstract::METHOD_MULTI_CREATE,
            );
            $methodName = $this->_helper->getMethodNameWithoutVersionSuffix($methodReflection);
            if (in_array($methodName, $methodsWithParentId)) {
                $isIdFieldExpected = true;
            }
        }
        return $isIdFieldExpected;
    }

    /**
     * Identify if method expects Resource ID to be present in the request.
     *
     * @param Zend\Server\Reflection\ReflectionMethod $methodReflection
     * @return bool
     */
    protected function _isResourceIdExpected(ReflectionMethod $methodReflection)
    {
        $isIdFieldExpected = false;
        $methodsWithId = array(
            Mage_Webapi_Controller_ActionAbstract::METHOD_GET,
            Mage_Webapi_Controller_ActionAbstract::METHOD_UPDATE,
            Mage_Webapi_Controller_ActionAbstract::METHOD_DELETE,
        );
        $methodName = $this->_helper->getMethodNameWithoutVersionSuffix($methodReflection);
        if (in_array($methodName, $methodsWithId)) {
            $isIdFieldExpected = true;
        }
        return $isIdFieldExpected;
    }

    /**
     * Retrieve the list of names of required params except ID and Request body.
     *
     * @param ReflectionMethod $methodReflection
     * @return array
     */
    protected function _getAdditionalRequiredParamNames(ReflectionMethod $methodReflection)
    {
        $paramNames = array();
        $methodInterfaces = $methodReflection->getPrototypes();
        /** Take the fullest interface that includes optional parameters also. */
        /** @var \Zend\Server\Reflection\Prototype $methodInterface */
        $methodInterface = end($methodInterfaces);
        $methodParams = $methodInterface->getParameters();
        $idParamName = $this->_helper->getOperationIdParamName($methodReflection);
        $bodyParamName = $this->_helper->getOperationBodyParamName($methodReflection);
        /** @var ReflectionParameter $paramReflection */
        foreach ($methodParams as $paramReflection) {
            if (!$paramReflection->isOptional()
                && $paramReflection->getName() != $bodyParamName
                && $paramReflection->getName() != $idParamName
            ) {
                $paramNames[] = $paramReflection->getName();
            }
        }
        return $paramNames;
    }

    /**
     * Generate list of possible routes taking into account optional params.
     *
     * Note: this is called recursively.
     *
     * @param array $optionalParams
     * @param string $basePath
     * @return array List of possible route params
     */
    /**
     * TODO: Assure that performance is not heavily impacted during routes match process.
     * It can happen due creation of routes with optional parameters. HTTP get parameters can be used for that.
     */
    protected function _getPathCombinations($optionalParams, $basePath)
    {
        $pathCombinations = array();
        /** Add current base path to the resulting array of routes. */
        $pathCombinations[] = $basePath;
        foreach ($optionalParams as $key => $paramName) {
            /** Add current param name to the route path and make recursive call. */
            $paramsWithoutCurrent = $optionalParams;
            unset($paramsWithoutCurrent[$key]);
            $currentPath = "$basePath/$paramName/:$paramName";
            $pathCombinations = array_merge(
                $pathCombinations,
                $this->_getPathCombinations(
                    $paramsWithoutCurrent,
                    $currentPath
                )
            );
        }
        return $pathCombinations;
    }

    /**
     * Retrieve all optional parameters names.
     *
     * @param ReflectionMethod $methodReflection
     * @return array
     */
    protected function _getOptionalParamNames(ReflectionMethod $methodReflection)
    {
        $optionalParamNames = array();
        $methodInterfaces = $methodReflection->getPrototypes();
        /** Take the fullest interface that includes optional parameters also. */
        /** @var \Zend\Server\Reflection\Prototype $methodInterface */
        $methodInterface = end($methodInterfaces);
        $methodParams = $methodInterface->getParameters();
        /** @var ReflectionParameter $paramReflection */
        foreach ($methodParams as $paramReflection) {
            if ($paramReflection->isOptional()) {
                $optionalParamNames[] = $paramReflection->getName();
            }
        }
        return $optionalParamNames;
    }
}
