<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Model\Rest\Swagger;

use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Reflection\TypeProcessor;
use Magento\Framework\Webapi\Authorization;
use Magento\Framework\Webapi\Exception as WebapiException;
use Magento\Webapi\Controller\Rest;
use Magento\Webapi\Model\AbstractSchemaGenerator;
use Magento\Webapi\Model\Config\Converter;
use Magento\Webapi\Model\Rest\Swagger;
use Magento\Webapi\Model\Rest\SwaggerFactory;
use Magento\Webapi\Model\ServiceMetadata;

/**
 * REST Swagger schema generator.
 *
 * Generate REST API description in a format of JSON document,
 * compliant with {@link https://github.com/swagger-api/swagger-spec/blob/master/versions/2.0.md Swagger specification}
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Generator extends AbstractSchemaGenerator
{
    /**
     * Error response schema
     */
    const ERROR_SCHEMA = '#/definitions/error-response';

    /**
     * Unauthorized description
     */
    const UNAUTHORIZED_DESCRIPTION = '401 Unauthorized';

    /** Array signifier */
    const ARRAY_SIGNIFIER = '[]';

    /**
     * Swagger factory instance.
     *
     * @var SwaggerFactory
     */
    protected $swaggerFactory;

    /**
     * Magento product metadata
     *
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * A map of Tags
     *
     * example:
     * [
     *     class1Name => tag information,
     *     class2Name => tag information,
     *     ...
     * ]
     *
     * @var array
     */
    protected $tags = [];

    /**
     * A map of definition
     *
     * example:
     * [
     *     definitionName1 => definition,
     *     definitionName2 => definition,
     *     ...
     * ]
     * Note: definitionName is converted from class name
     * @var array
     */
    protected $definitions = [];

    /**
     * List of simple parameter types not to be processed by the definitions generator
     * Contains  mapping to the internal swagger simple types
     *
     * @var string[]
     */
    protected $simpleTypeList = [
        'bool'                              => 'boolean',
        'boolean'                           => 'boolean',
        'int'                               => 'integer',
        'integer'                           => 'integer',
        'double'                            => 'number',
        'float'                             => 'number',
        'number'                            => 'number',
        'string'                            => 'string',
        TypeProcessor::ANY_TYPE             => 'string',
        TypeProcessor::NORMALIZED_ANY_TYPE  => 'string',
    ];

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Webapi\Model\Cache\Type\Webapi $cache
     * @param \Magento\Framework\Reflection\TypeProcessor $typeProcessor
     * @param \Magento\Framework\Webapi\CustomAttributeTypeLocatorInterface $customAttributeTypeLocator
     * @param \Magento\Webapi\Model\ServiceMetadata $serviceMetadata
     * @param Authorization $authorization
     * @param SwaggerFactory $swaggerFactory
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     */
    public function __construct(
        \Magento\Webapi\Model\Cache\Type\Webapi $cache,
        \Magento\Framework\Reflection\TypeProcessor $typeProcessor,
        \Magento\Framework\Webapi\CustomAttributeTypeLocatorInterface $customAttributeTypeLocator,
        \Magento\Webapi\Model\ServiceMetadata $serviceMetadata,
        Authorization $authorization,
        SwaggerFactory $swaggerFactory,
        ProductMetadataInterface $productMetadata
    ) {
        $this->swaggerFactory = $swaggerFactory;
        $this->productMetadata = $productMetadata;
        parent::__construct(
            $cache,
            $typeProcessor,
            $customAttributeTypeLocator,
            $serviceMetadata,
            $authorization
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function generateSchema($requestedServiceMetadata, $requestScheme, $requestHost, $endpointUrl)
    {
        /** @var Swagger $swagger */
        $swagger = $this->swaggerFactory->create();

        $swagger->setInfo($this->getGeneralInfo());

        $this->addCustomAttributeTypes();
        $swagger->setHost($requestHost);
        $swagger->setBasePath(strstr($endpointUrl, Rest::SCHEMA_PATH, true));
        $swagger->setSchemes([$requestScheme]);

        foreach ($requestedServiceMetadata as $serviceName => $serviceData) {
            if (!isset($this->tags[$serviceName])) {
                $this->tags[$serviceName] = $this->generateTagInfo($serviceName, $serviceData);
                $swagger->addTag($this->tags[$serviceName]);
            }
            foreach ($serviceData[Converter::KEY_ROUTES] as $uri => $httpMethods) {
                $uri = $this->convertPathParams($uri);
                foreach ($httpMethods as $httpOperation => $httpMethodData) {
                    $httpOperation = strtolower($httpOperation);
                    $phpMethodData = $serviceData[Converter::KEY_METHODS][$httpMethodData[Converter::KEY_METHOD]];
                    $httpMethodData[Converter::KEY_METHOD] = $phpMethodData;
                    $httpMethodData['uri'] = $uri;
                    $httpMethodData['httpOperation'] = $httpOperation;
                    $swagger->addPath(
                        $this->convertPathParams($uri),
                        $httpOperation,
                        $this->generatePathInfo($httpOperation, $httpMethodData, $serviceName)
                    );
                }
            }
        }
        $swagger->setDefinitions($this->getDefinitions());

        return $swagger->toSchema();
    }

    /**
     * Get the 'Info' section data
     *
     * @return string[]
     */
    protected function getGeneralInfo()
    {
        $versionParts = explode('.', $this->productMetadata->getVersion());
        if (!isset($versionParts[0]) || !isset($versionParts[1])) {
            return []; // Major and minor version are not set - return empty response
        }
        $majorMinorVersion = $versionParts[0] . '.' . $versionParts[1];

        return [
            'version' => $majorMinorVersion,
            'title' => $this->productMetadata->getName() . ' ' . $this->productMetadata->getEdition(),
        ];
    }

    /**
     * Generate path info based on method data
     *
     * @param string $methodName
     * @param array $httpMethodData
     * @param string $tagName
     * @return array
     */
    protected function generatePathInfo($methodName, $httpMethodData, $tagName)
    {
        $methodData = $httpMethodData[Converter::KEY_METHOD];
        $pathInfo = [
            'tags' => [$tagName],
            'description' => $methodData['documentation'],
            'operationId' => $this->typeProcessor->getOperationName($tagName, $methodData[Converter::KEY_METHOD]) .
                ucfirst($methodName)
        ];

        $parameters = $this->generateMethodParameters($httpMethodData);
        if ($parameters) {
            $pathInfo['parameters'] = $parameters;
        }
        $pathInfo['responses'] = $this->generateMethodResponses($methodData);

        return $pathInfo;
    }

    /**
     * Generate response based on method data
     *
     * @param array $methodData
     * @return array
     */
    protected function generateMethodResponses($methodData)
    {
        $responses = [];

        if (isset($methodData['interface']['out']['parameters'])
            && is_array($methodData['interface']['out']['parameters'])
        ) {
            $parameters = $methodData['interface']['out']['parameters'];
            $responses = $this->generateMethodSuccessResponse($parameters, $responses);
        }

        /** Handle authorization exceptions that may not be documented */
        if (isset($methodData['resources'])) {
            foreach ($methodData['resources'] as $resourceName) {
                if ($resourceName !== 'anonymous') {
                    $responses[WebapiException::HTTP_UNAUTHORIZED]['description'] = self::UNAUTHORIZED_DESCRIPTION;
                    $responses[WebapiException::HTTP_UNAUTHORIZED]['schema']['$ref'] = self::ERROR_SCHEMA;
                    break;
                }
            }
        }

        if (isset($methodData['interface']['out']['throws'])
            && is_array($methodData['interface']['out']['throws'])
        ) {
            foreach ($methodData['interface']['out']['throws'] as $exceptionClass) {
                $responses = $this->generateMethodExceptionErrorResponses($exceptionClass, $responses);
            }
        }
        $responses['default']['description'] = 'Unexpected error';
        $responses['default']['schema']['$ref'] = self::ERROR_SCHEMA;

        return $responses;
    }

    /**
     * Generate parameters based on method data
     *
     * @param array $httpMethodData
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function generateMethodParameters($httpMethodData)
    {
        $bodySchema = [];
        $parameters = [];

        $phpMethodData = $httpMethodData[Converter::KEY_METHOD];
        /** Return nothing if necessary fields are not set */
        if (!isset($phpMethodData['interface']['in']['parameters'])
            || !isset($httpMethodData['uri'])
            || !isset($httpMethodData['httpOperation'])
        ) {
            return [];
        }

        foreach ($phpMethodData['interface']['in']['parameters'] as $parameterName => $parameterInfo) {
            /** Omit forced parameters */
            if (isset($httpMethodData['parameters'][$parameterName]['force'])
                && $httpMethodData['parameters'][$parameterName]['force']
            ) {
                continue;
            }

            if (!isset($parameterInfo['type'])) {
                return [];
            }
            $description = isset($parameterInfo['documentation']) ? $parameterInfo['documentation'] : null;

            /** Get location of parameter */
            if (strpos($httpMethodData['uri'], '{' . $parameterName . '}') !== false) {
                $parameters[] = $this->generateMethodPathParameter($parameterName, $parameterInfo, $description);
            } elseif (strtoupper($httpMethodData['httpOperation']) === 'GET') {
                $parameters = $this->generateMethodQueryParameters(
                    $parameterName,
                    $parameterInfo,
                    $description,
                    $parameters
                );
            } else {
                $bodySchema = $this->generateBodySchema(
                    $parameterName,
                    $parameterInfo,
                    $description,
                    $bodySchema
                );
            }
        }

        /**
         * Add all the path params that don't correspond directly the PHP parameters
         */
        preg_match_all('#\\{([^\\{\\}]*)\\}#', $httpMethodData['uri'], $allPathParams);
        $remainingPathParams = array_diff(
            $allPathParams[1],
            array_keys($phpMethodData['interface']['in']['parameters'])
        );
        foreach ($remainingPathParams as $pathParam) {
            $parameters[] = [
                'name' => $pathParam,
                'in' => 'path',
                'type' => 'string',
                'required' => true
            ];
        }

        if ($bodySchema) {
            $bodyParam = [];
            $bodyParam['name'] = '$body';
            $bodyParam['in'] = 'body';
            $bodyParam['schema'] = $bodySchema;
            $parameters[] = $bodyParam;
        }
        return $parameters;
    }

    /**
     * Creates an array for the given query parameter
     *
     * @param string $name
     * @param string $type
     * @param string $description
     * @param bool|null $required
     * @return array
     */
    private function createQueryParam($name, $type, $description, $required = null)
    {
        $param = [
            'name' => $name,
            'in' => 'query',
        ];

        $param = array_merge($param, $this->getObjectSchema($type, $description));

        if (isset($required)) {
            $param['required'] = $required;
        }
        return $param;
    }

    /**
     * Generate Tag Info for given service
     *
     * @param string $serviceName
     * @param array $serviceData
     * @return string[]
     */
    protected function generateTagInfo($serviceName, $serviceData)
    {
        $tagInfo = [];
        $tagInfo['name'] = $serviceName;
        if (!empty($serviceData) && is_array($serviceData)) {
            $tagInfo['description'] = $serviceData[Converter::KEY_DESCRIPTION];
        }
        return $tagInfo;
    }

    /**
     * Generate definition for given type
     *
     * @param string $typeName
     * @param string $description
     * @return array
     */
    protected function getObjectSchema($typeName, $description = '')
    {
        $simpleType = $this->getSimpleType($typeName);
        if ($simpleType == false) {
            $result = ['type' => 'array'];
            if (!empty($description)) {
                $result['description'] = $description;
            }
            $trimedTypeName = rtrim($typeName, '[]');
            if ($simpleType = $this->getSimpleType($trimedTypeName)) {
                $result['items'] = ['type' => $simpleType];
            } else {
                if (strpos($typeName, '[]')) {
                    $result['items'] = ['$ref' => $this->getDefinitionReference($trimedTypeName)];
                } else {
                    $result = ['$ref' => $this->getDefinitionReference($trimedTypeName)];
                }
                if (!$this->isDefinitionExists($trimedTypeName)) {
                    $definitionKey = $this->toLowerCaseDashSeparated($trimedTypeName);
                    $this->definitions[$definitionKey] = [];
                    $this->definitions[$definitionKey] = $this->generateDefinition($trimedTypeName);
                }
            }
        } else {
            $result = ['type' => $simpleType];
            if (!empty($description)) {
                $result['description'] = $description;
            }
        }
        return $result;
    }

    /**
     * Generate definition for given type
     *
     * @param string $typeName
     * @return array
     */
    protected function generateDefinition($typeName)
    {
        $properties = [];
        $requiredProperties = [];
        $typeData = $this->typeProcessor->getTypeData($typeName);
        if (isset($typeData['parameters'])) {
            foreach ($typeData['parameters'] as $parameterName => $parameterData) {
                $properties[$parameterName] = $this->getObjectSchema(
                    $parameterData['type'],
                    $parameterData['documentation']
                );
                if ($parameterData['required']) {
                    $requiredProperties[] = $parameterName;
                }
            }
        }
        $definition = ['type' => 'object'];
        if (isset($typeData['documentation'])) {
            $definition['description'] = $typeData['documentation'];
        }
        if (!empty($properties)) {
            $definition['properties'] = $properties;
        }
        if (!empty($requiredProperties)) {
            $definition['required'] = $requiredProperties;
        }
        return $definition;
    }

    /**
     * Get definitions
     *
     * @return array
     * Todo: create interface for error response
     */
    protected function getDefinitions()
    {
        return array_merge(
            [
                'error-response' => [
                    'type' => 'object',
                    'properties' => [
                        'message' => [
                            'type' => 'string',
                            'description' => 'Error message',
                        ],
                        'errors' => [
                            '$ref' => '#/definitions/error-errors',
                        ],
                        'code' => [
                            'type' => 'integer',
                            'description' => 'Error code',
                        ],
                        'parameters' => [
                            '$ref' => '#/definitions/error-parameters',
                        ],
                        'trace' => [
                            'type' => 'string',
                            'description' => 'Stack trace',
                        ],
                    ],
                    'required' => ['message'],
                ],
                'error-errors' => [
                    'type' => 'array',
                    'description' => 'Errors list',
                    'items' => [
                        '$ref' => '#/definitions/error-errors-item',
                    ],
                ],
                'error-errors-item' => [
                    'type' => 'object',
                    'description' => 'Error details',
                    'properties' => [
                        'message' => [
                            'type' => 'string',
                            'description' => 'Error message',
                        ],
                        'parameters' => [
                            '$ref' => '#/definitions/error-parameters',
                        ],
                    ],
                ],
                'error-parameters' => [
                    'type' => 'array',
                    'description' => 'Error parameters list',
                    'items' => [
                        '$ref' => '#/definitions/error-parameters-item',
                    ],
                ],
                'error-parameters-item' => [
                    'type' => 'object',
                    'description' => 'Error parameters item',
                    'properties' => [
                        'resources' => [
                            'type' => 'string',
                            'description' => 'ACL resource',
                        ],
                        'fieldName' => [
                            'type' => 'string',
                            'description' => 'Missing or invalid field name'
                        ],
                        'fieldValue' => [
                            'type' => 'string',
                            'description' => 'Incorrect field value'
                        ],
                    ],
                ],
            ],
            $this->snakeCaseDefinitions($this->definitions)
        );
    }

    /**
     * Converts definitions' properties array to snake_case.
     *
     * @param array $definitions
     * @return array
     */
    private function snakeCaseDefinitions($definitions)
    {
        foreach ($definitions as $name => $vals) {
            if (!empty($vals['properties'])) {
                $definitions[$name]['properties'] = $this->convertArrayToSnakeCase($vals['properties']);
            }
            if (!empty($vals['required'])) {
                $snakeCaseRequired = [];
                foreach ($vals['required'] as $requiredProperty) {
                    $snakeCaseRequired[] = SimpleDataObjectConverter::camelCaseToSnakeCase($requiredProperty);
                }
                $definitions[$name]['required'] = $snakeCaseRequired;
            }
        }
        return $definitions;
    }

    /**
     * Converts associative array's key names from camelCase to snake_case, recursively.
     *
     * @param array $properties
     * @return array
     */
    private function convertArrayToSnakeCase($properties)
    {
        foreach ($properties as $name => $value) {
            $snakeCaseName = SimpleDataObjectConverter::camelCaseToSnakeCase($name);
            if (is_array($value)) {
                $value = $this->convertArrayToSnakeCase($value);
            }
            unset($properties[$name]);
            $properties[$snakeCaseName] = $value;
        }
        return $properties;
    }

    /**
     * Get definition reference
     *
     * @param string $typeName
     * @return string
     */
    protected function getDefinitionReference($typeName)
    {
        return '#/definitions/' . $this->toLowerCaseDashSeparated($typeName);
    }

    /**
     * Get the CamelCased type name in 'hyphen-separated-lowercase-words' format
     *
     * e.g. test-module5-v1-entity-all-soap-and-rest
     *
     * @param string $typeName
     * @return string
     */
    protected function toLowerCaseDashSeparated($typeName)
    {
        return strtolower(preg_replace('/(.)([A-Z])/', "$1-$2", $typeName));
    }

    /**
     * Check if definition exists
     *
     * @param string $typeName
     * @return bool
     */
    protected function isDefinitionExists($typeName)
    {
        return isset($this->definitions[$this->toLowerCaseDashSeparated($typeName)]);
    }

    /**
     * Create and add custom attribute types
     *
     * @return void
     */
    protected function addCustomAttributeTypes()
    {
        foreach ($this->customAttributeTypeLocator->getAllServiceDataInterfaces() as $customAttributeClass) {
            $this->typeProcessor->register($customAttributeClass);
        }
    }

    /**
     * Get service metadata
     *
     * @param string $serviceName
     * @return array
     */
    protected function getServiceMetadata($serviceName)
    {
        return $this->serviceMetadata->getRouteMetadata($serviceName);
    }

    /**
     * Get the simple type supported by Swagger, or false if type is not simple
     *
     * @param string $type
     * @return bool|string
     */
    protected function getSimpleType($type)
    {
        if (array_key_exists($type, $this->simpleTypeList)) {
            return $this->simpleTypeList[$type];
        } else {
            return false;
        }
    }

    /**
     * Return the parameter names to describe a given parameter, mapped to the respective type
     *
     * Query parameters may be complex types, and multiple parameters will be listed in the schema to outline
     * the structure of the type.
     *
     * @param string $name
     * @param string $type
     * @param string $description
     * @param string $prefix
     * @return string[]
     */
    protected function getQueryParamNames($name, $type, $description, $prefix = '')
    {
        if ($this->typeProcessor->isTypeSimple($type)) {
            // Primitive type or array of primitive types
            return [
                $this->handlePrimitive($name, $prefix) => [
                    'type' => substr($type, -2) === '[]' ? $type : $this->getSimpleType($type),
                    'description' => $description
                ]
            ];
        }
        if ($this->typeProcessor->isArrayType($type)) {
            // Array of complex type
            $arrayType = substr($type, 0, -2);
            return $this->handleComplex($name, $arrayType, $prefix, true);
        } else {
            // Complex type
            return $this->handleComplex($name, $type, $prefix, false);
        }
    }

    /**
     * Recursively generate the query param names for a complex type
     *
     * @param string $name
     * @param string $type
     * @param string $prefix
     * @param bool $isArray
     * @return string[]
     */
    private function handleComplex($name, $type, $prefix, $isArray)
    {
        $parameters = $this->typeProcessor->getTypeData($type)['parameters'];
        $queryNames = [];
        foreach ($parameters as $subParameterName => $subParameterInfo) {
            $subParameterType = $subParameterInfo['type'];
            $subParameterDescription = isset($subParameterInfo['documentation'])
                ? $subParameterInfo['documentation']
                : null;
            $subPrefix = $prefix
                ? $prefix . '[' . $name . ']'
                : $name;
            if ($isArray) {
                $subPrefix .= self::ARRAY_SIGNIFIER;
            }
            $queryNames = array_merge(
                $queryNames,
                $this->getQueryParamNames($subParameterName, $subParameterType, $subParameterDescription, $subPrefix)
            );
        }
        return $queryNames;
    }

    /**
     * Generate the query param name for a primitive type
     *
     * @param string $name
     * @param string $prefix
     * @return string
     */
    private function handlePrimitive($name, $prefix)
    {
        return $prefix
            ? $prefix . '[' . $name . ']'
            : $name;
    }

    /**
     * Convert path parameters from :param to {param}
     *
     * @param string $uri
     * @return string
     */
    private function convertPathParams($uri)
    {
        $parts = explode('/', $uri);
        for ($i=0; $i < count($parts); $i++) {
            if (strpos($parts[$i], ':') === 0) {
                $parts[$i] = '{' . substr($parts[$i], 1) . '}';
            }
        }
        return implode('/', $parts);
    }

    /**
     * Generate method path parameter
     *
     * @param string $parameterName
     * @param array $parameterInfo
     * @param string $description
     * @return string[]
     */
    private function generateMethodPathParameter($parameterName, $parameterInfo, $description)
    {
        $param = [
            'name' => $parameterName,
            'in' => 'path',
            'type' => $this->getSimpleType($parameterInfo['type']),
            'required' => true
        ];
        if ($description) {
            $param['description'] = $description;
            return $param;
        }
        return $param;
    }

    /**
     * Generate method query parameters
     *
     * @param string $parameterName
     * @param array $parameterInfo
     * @param string $description
     * @param array $parameters
     * @return array
     */
    private function generateMethodQueryParameters($parameterName, $parameterInfo, $description, $parameters)
    {
        $queryParams = $this->getQueryParamNames($parameterName, $parameterInfo['type'], $description);
        if (count($queryParams) === 1) {
            // handle simple query parameter (includes the 'required' field)
            $parameters[] = $this->createQueryParam(
                $parameterName,
                $parameterInfo['type'],
                $description,
                $parameterInfo['required']
            );
        } else {
            /**
             * Complex query parameters are represented by a set of names which describes the object's fields.
             *
             * Omits the 'required' field.
             */
            foreach ($queryParams as $name => $queryParamInfo) {
                $parameters[] = $this->createQueryParam(
                    $name,
                    $queryParamInfo['type'],
                    $queryParamInfo['description']
                );
            }
        }
        return $parameters;
    }

    /**
     * Generate body schema
     *
     * @param string $parameterName
     * @param array $parameterInfo
     * @param string $description
     * @param array $bodySchema
     * @return array
     */
    private function generateBodySchema($parameterName, $parameterInfo, $description, $bodySchema)
    {
        $required = isset($parameterInfo['required']) ? $parameterInfo['required'] : null;
        /*
         * There can only be one body parameter, multiple PHP parameters are represented as different
         * properties of the body.
         */
        if ($required) {
            $bodySchema['required'][] = $parameterName;
        }
        $bodySchema['properties'][$parameterName] = $this->getObjectSchema(
            $parameterInfo['type'],
            $description
        );
        $bodySchema['type'] = 'object';
        return $bodySchema;
    }

    /**
     * Generate method 200 response
     *
     * @param array $parameters
     * @param array $responses
     * @return array
     */
    private function generateMethodSuccessResponse($parameters, $responses)
    {
        if (isset($parameters['result']) && is_array($parameters['result'])) {
            $description = '';
            if (isset($parameters['result']['documentation'])) {
                $description = $parameters['result']['documentation'];
            }
            $schema = [];
            if (isset($parameters['result']['type'])) {
                $schema = $this->getObjectSchema($parameters['result']['type'], $description);
            }
            $responses['200']['description'] = '200 Success.';
            if (!empty($schema)) {
                $responses['200']['schema'] = $schema;
            }
        }
        return $responses;
    }

    /**
     * Generate method exception error responses
     *
     * @param array $exceptionClass
     * @param array $responses
     * @return array
     */
    private function generateMethodExceptionErrorResponses($exceptionClass, $responses)
    {
        $httpCode = '500';
        $description = 'Internal Server error';
        if (is_subclass_of($exceptionClass, \Magento\Framework\Exception\LocalizedException::class)) {
            // Map HTTP codes for LocalizedExceptions according to exception type
            if (is_subclass_of($exceptionClass, \Magento\Framework\Exception\NoSuchEntityException::class)) {
                $httpCode = WebapiException::HTTP_NOT_FOUND;
                $description = '404 Not Found';
            } elseif (is_subclass_of($exceptionClass, \Magento\Framework\Exception\AuthorizationException::class)
                || is_subclass_of($exceptionClass, \Magento\Framework\Exception\AuthenticationException::class)
            ) {
                $httpCode = WebapiException::HTTP_UNAUTHORIZED;
                $description = self::UNAUTHORIZED_DESCRIPTION;
            } else {
                // Input, Expired, InvalidState exceptions will fall to here
                $httpCode = WebapiException::HTTP_BAD_REQUEST;
                $description = '400 Bad Request';
            }
        }
        $responses[$httpCode]['description'] = $description;
        $responses[$httpCode]['schema']['$ref'] = self::ERROR_SCHEMA;

        return $responses;
    }

    /**
     * Retrieve a list of services visible to current user.
     *
     * @return string[]
     */
    public function getListOfServices()
    {
        $listOfAllowedServices = [];
        foreach ($this->serviceMetadata->getServicesConfig() as $serviceName => $service) {
            foreach ($service[ServiceMetadata::KEY_SERVICE_METHODS] as $method) {
                if ($this->authorization->isAllowed($method[ServiceMetadata::KEY_ACL_RESOURCES])) {
                    $listOfAllowedServices[] = $serviceName;
                    break;
                }
            }
        }
        return $listOfAllowedServices;
    }
}
