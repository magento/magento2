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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Webapi\Model;

use Zend\Code\Reflection\ClassReflection;
use Zend\Code\Reflection\MethodReflection;
use Magento\Framework\Service\SimpleDataObjectConverter;
use Magento\Framework\Service\Data\AttributeValue;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\ObjectManager;
use Magento\Webapi\Model\Config\ClassReflector\TypeProcessor;
use Magento\Webapi\Model\Cache\Type as WebapiCache;

/**
 * Data object processor for de-serialization using class reflection
 */
class DataObjectProcessor
{
    const IS_METHOD_PREFIX = 'is';
    const HAS_METHOD_PREFIX = 'has';
    const GETTER_PREFIX = 'get';
    const SERVICE_INTERFACE_METHODS_CACHE_PREFIX = 'serviceInterfaceMethodsMap';
    const BASE_MODEL_CLASS = 'Magento\Framework\Model\AbstractExtensibleModel';

    /**
     * @var WebapiCache
     */
    protected $cache;

    /**
     * @var TypeProcessor
     */
    protected $typeProcessor;

    /**
     * @var array
     */
    protected $dataInterfaceMethodsMap = [];

    /**
     * @var array
     */
    protected $serviceInterfaceMethodsMap = [];

    /**
     * Initialize dependencies.
     *
     * @param WebapiCache $cache
     * @param TypeProcessor $typeProcessor
     */
    public function __construct(WebapiCache $cache, TypeProcessor $typeProcessor)
    {
        $this->cache = $cache;
        $this->typeProcessor = $typeProcessor;
    }

    /**
     * Use class reflection on given data interface to build output data array
     *
     * @param mixed $dataObject
     * @param string $dataObjectType
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function buildOutputDataArray($dataObject, $dataObjectType)
    {
        $methods = $this->getMethodsMap($dataObjectType);
        $outputData = [];

        /** @var MethodReflection $method */
        foreach ($methods as $methodName => $methodReflectionData) {
            // any method with parameter(s) gets ignored because we do not know the type and value of
            // the parameter(s), so we are not able to process
            if ($methodReflectionData['parameterCount'] > 0) {
                continue;
            }
            $returnType = $methodReflectionData['type'];
            if (substr($methodName, 0, 2) === self::IS_METHOD_PREFIX) {
                $value = $dataObject->{$methodName}();
                if ($value === null && !$methodReflectionData['isRequired']) {
                    continue;
                }
                $key = SimpleDataObjectConverter::camelCaseToSnakeCase(substr($methodName, 2));
                $outputData[$key] = $this->castValueToType($value, $returnType);
            } else if (substr($methodName, 0, 3) === self::HAS_METHOD_PREFIX) {
                $value = $dataObject->{$methodName}();
                if ($value === null && !$methodReflectionData['isRequired']) {
                    continue;
                }
                $key = SimpleDataObjectConverter::camelCaseToSnakeCase(substr($methodName, 3));
                $outputData[$key] = $this->castValueToType($value, $returnType);
            } else if (substr($methodName, 0, 3) === self::GETTER_PREFIX) {
                $value = $dataObject->{$methodName}();
                if ($methodName === 'getCustomAttributes' && $value === []) {
                    continue;
                }
                if ($value === null && !$methodReflectionData['isRequired']) {
                    continue;
                }
                $key = SimpleDataObjectConverter::camelCaseToSnakeCase(substr($methodName, 3));
                if ($key === AbstractExtensibleModel::CUSTOM_ATTRIBUTES_KEY) {
                    $value = $this->convertCustomAttributes($value);
                } else if (is_object($value)) {
                    $value = $this->buildOutputDataArray($value, $returnType);
                } else if (is_array($value)) {
                    $valueResult = array();
                    $arrayElementType = substr($returnType, 0, -2);
                    foreach ($value as $singleValue) {
                        if (is_object($singleValue)) {
                            $singleValue = $this->buildOutputDataArray($singleValue, $arrayElementType);
                        }
                        $valueResult[] = $this->castValueToType($singleValue, $arrayElementType);
                    }
                    $value = $valueResult;
                }
                $outputData[$key] = $this->castValueToType($value, $returnType);
            }
        }
        return $outputData;
    }

    /**
     * Cast the output type to the documented type. This helps for output purposes.
     *
     * @param mixed $value
     * @param string $type
     * @return mixed
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function castValueToType($value, $type)
    {
        if ($value === null) {
            return null;
        }

        if ($type === "int" || $type === "integer") {
            return (int)$value;
        }

        if ($type === "string") {
            return (string)$value;
        }

        if ($type === "bool" || $type === "boolean" || $type === "true" || $type == "false") {
            return (bool)$value;
        }

        if ($type === "float") {
            return (float)$value;
        }

        if ($type === "double") {
            return (double)$value;
        }

        return $value;
    }

    /**
     * Get return type by interface name and method
     *
     * @param string $interfaceName
     * @param string $methodName
     * @return string
     */
    public function getMethodReturnType($interfaceName, $methodName)
    {
        return $this->getMethodsMap($interfaceName)[$methodName]['type'];
    }

    /**
     * Convert array of custom_attributes to use flat array structure
     *
     * @param \Magento\Framework\Api\Data\AttributeInterface[] $customAttributes
     * @return array
     */
    protected function convertCustomAttributes($customAttributes)
    {
        $result = array();
        foreach ((array)$customAttributes as $customAttribute) {
            $result[] = $this->convertCustomAttribute($customAttribute);
        }
        return $result;
    }

    /**
     * Convert custom_attribute object to use flat array structure
     *
     * @param \Magento\Framework\Api\Data\AttributeInterface $customAttribute
     * @return array
     */
    protected function convertCustomAttribute($customAttribute)
    {
        $data = array();
        $data[AttributeValue::ATTRIBUTE_CODE] = $customAttribute->getAttributeCode();
        $value = $customAttribute->getValue();
        if (is_object($value)) {
            $value = $this->buildOutputDataArray($value, get_class($value));
        } else if (is_array($value)) {
            $valueResult = array();
            foreach ($value as $singleValue) {
                if (is_object($singleValue)) {
                    $elementType = get_class($singleValue);
                    $singleValue = $this->buildOutputDataArray($singleValue, $elementType);
                }
                // Cannot cast to a type because the type is unknown
                $valueResult[] = $singleValue;
            }
            $value = $valueResult;
        }
        $data[AttributeValue::VALUE] = $value;
        return $data;
    }

    /**
     * Return service interface or Data interface methods loaded from cache
     *
     * @param string $interfaceName
     * @return array
     * <pre>
     * Service methods' reflection data stored in cache as 'methodName' => 'returnType'
     * ex.
     * [
     *  'create' => '\Magento\Customer\Api\Data\Customer',
     *  'validatePassword' => 'boolean'
     * ]
     * </pre>
     */
    public function getMethodsMap($interfaceName)
    {
        $key = self::SERVICE_INTERFACE_METHODS_CACHE_PREFIX . "-" . md5($interfaceName);
        if (!isset($this->serviceInterfaceMethodsMap[$key])) {
            $methodMap = $this->cache->load($key);
            if ($methodMap) {
                $this->serviceInterfaceMethodsMap[$key] = unserialize($methodMap);
            } else {
                $methodMap = $this->getMethodMapViaReflection($interfaceName);
                $this->serviceInterfaceMethodsMap[$key] = $methodMap;
                $this->cache->save(serialize($this->serviceInterfaceMethodsMap[$key]), $key);
            }
        }
        return $this->serviceInterfaceMethodsMap[$key];
    }

    /**
     * Use reflection to load the method information
     *
     * @param string $interfaceName
     * @return array
     */
    protected function getMethodMapViaReflection($interfaceName)
    {
        $methodMap = [];
        $class = new ClassReflection($interfaceName);
        $baseClassMethods = false;
        foreach ($class->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            // Include all the methods of classes inheriting from AbstractExtensibleObject.
            // Ignore all the methods of AbstractExtensibleModel's parent classes
            if ($method->class === self::BASE_MODEL_CLASS) {
                $baseClassMethods = true;
            } elseif ($baseClassMethods) {
                // ReflectionClass::getMethods() sorts the methods by class (lowest in inheritance tree first)
                // then by the order they are defined in the class definition
                break;
            }

            if ($this->isSuitableMethod($method)) {
                $methodMap[$method->getName()] = $this->typeProcessor->getGetterReturnType($method);
            }
        }
        return $methodMap;
    }

    /**
     * Determines if the method is suitable to be used by the processor.
     *
     * @param \ReflectionMethod $method
     * @return bool
     */
    protected function isSuitableMethod($method)
    {
        $isSuitableMethodType = !($method->isConstructor() || $method->isFinal()
            || $method->isStatic() || $method->isDestructor());

        $isExcludedMagicMethod = in_array(
            $method->getName(),
            ['__sleep', '__wakeup', '__clone']
        );
        return $isSuitableMethodType && !$isExcludedMagicMethod;
    }
}
