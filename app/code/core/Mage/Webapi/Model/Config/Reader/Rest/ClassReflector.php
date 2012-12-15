<?php
use Zend\Server\Reflection\ReflectionMethod;

/**
 * REST API specific class reflector.
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
class Mage_Webapi_Model_Config_Reader_Rest_ClassReflector extends Mage_Webapi_Model_Config_Reader_ClassReflectorAbstract
{
    /** @var Mage_Webapi_Model_Config_Reader_Rest_RouteGenerator */
    protected $_routeGenerator;

    /**
     * Construct reflector with route generator.
     *
     * @param Mage_Webapi_Helper_Config $helper
     * @param Mage_Webapi_Model_Config_Reader_TypeProcessor $typeProcessor
     * @param Mage_Webapi_Model_Config_Reader_Rest_RouteGenerator $routeGenerator
     */
    public function __construct(
        Mage_Webapi_Helper_Config $helper,
        Mage_Webapi_Model_Config_Reader_TypeProcessor $typeProcessor,
        Mage_Webapi_Model_Config_Reader_Rest_RouteGenerator $routeGenerator
    ) {
        parent::__construct($helper, $typeProcessor);
        $this->_routeGenerator = $routeGenerator;
    }

    /**
     * Set types and REST routes data into reader after reflecting all files.
     *
     * @return array
     */
    public function getPostReflectionData()
    {
        return array(
            'types' => $this->_typeProcessor->getTypesData(),
            'type_to_class_map' => $this->_typeProcessor->getTypeToClassMap(),
            'rest_routes' => $this->_routeGenerator->getRoutes(),
        );
    }

    /**
     * Add REST routes to method data.
     *
     * @param Zend\Server\Reflection\ReflectionMethod $method
     * @return array
     */
    public function extractMethodData(ReflectionMethod $method)
    {
        $methodData = parent::extractMethodData($method);
        $restRoutes = $this->_routeGenerator->generateRestRoutes($method);
        $methodData['rest_routes'] = array_keys($restRoutes);

        return $methodData;
    }
}
