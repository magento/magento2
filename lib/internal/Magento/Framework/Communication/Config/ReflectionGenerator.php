<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Communication\Config;

use Magento\Framework\Communication\ConfigInterface as Config;
use Magento\Framework\Reflection\MethodsMap;

/**
 * Communication config generator based on service methods reflection
 */
class ReflectionGenerator
{
    const DEFAULT_HANDLER = 'defaultHandler';
    /**
     * @var MethodsMap
     */
    private $methodsMap;

    /**
     * Initialize dependencies
     *
     * @param MethodsMap $methodsMap
     */
    public function __construct(MethodsMap $methodsMap)
    {
        $this->methodsMap = $methodsMap;
    }

    /**
     * Extract service method metadata.
     *
     * @param string $className
     * @param string $methodName
     * @return array
     */
    public function extractMethodMetadata($className, $methodName)
    {
        $result = [
            Config::SCHEMA_METHOD_PARAMS => [],
            Config::SCHEMA_METHOD_RETURN_TYPE => $this->methodsMap->getMethodReturnType($className, $methodName),
            Config::SCHEMA_METHOD_HANDLER => [Config::HANDLER_TYPE => $className, Config::HANDLER_METHOD => $methodName]
        ];
        $paramsMeta = $this->methodsMap->getMethodParams($className, $methodName);
        foreach ($paramsMeta as $paramPosition => $paramMeta) {
            $result[Config::SCHEMA_METHOD_PARAMS][] = [
                Config::SCHEMA_METHOD_PARAM_NAME => $paramMeta[MethodsMap::METHOD_META_NAME],
                Config::SCHEMA_METHOD_PARAM_POSITION => $paramPosition,
                Config::SCHEMA_METHOD_PARAM_IS_REQUIRED => !$paramMeta[MethodsMap::METHOD_META_HAS_DEFAULT_VALUE],
                Config::SCHEMA_METHOD_PARAM_TYPE => $paramMeta[MethodsMap::METHOD_META_TYPE],
            ];
        }
        return $result;
    }

    /**
     * Generate config data based on service method signature.
     *
     * @param string $topicName
     * @param string $serviceType
     * @param string $serviceMethod
     * @param array|null $handlers
     * @return array
     */
    public function generateTopicConfigForServiceMethod($topicName, $serviceType, $serviceMethod, $handlers = [])
    {
        $methodMetadata = $this->extractMethodMetadata($serviceType, $serviceMethod);
        $returnType = $methodMetadata[Config::SCHEMA_METHOD_RETURN_TYPE];
        $returnType = ($returnType != 'void' && $returnType != 'null') ? $returnType : null;
        return [
            Config::TOPIC_NAME => $topicName,
            Config::TOPIC_IS_SYNCHRONOUS => $returnType ? true : false,
            Config::TOPIC_REQUEST => $methodMetadata[Config::SCHEMA_METHOD_PARAMS],
            Config::TOPIC_REQUEST_TYPE => Config::TOPIC_REQUEST_TYPE_METHOD,
            Config::TOPIC_RESPONSE => $returnType,
            Config::TOPIC_HANDLERS => $handlers
                ?: [self::DEFAULT_HANDLER => $methodMetadata[Config::SCHEMA_METHOD_HANDLER]]
        ];
    }
}
