<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Code\Generator;

use Magento\Framework\Code\Generator\DefinedClasses;
use Magento\Framework\Code\Generator\Io;
use Magento\Framework\Communication\ConfigInterface as CommunicationConfig;
use Magento\Framework\Reflection\MethodsMap as ServiceMethodsMap;
use Magento\Framework\Communication\Config\Reader\RemoteServiceReader as CommunicationRemoteServiceReader;

/**
 * Code generator for remote services.
 */
class RemoteServiceGenerator extends \Magento\Framework\Code\Generator\EntityAbstract
{
    const ENTITY_TYPE = 'remote';
    const REMOTE_SERVICE_SUFFIX = 'Remote';

    /**
     * @var CommunicationConfig
     */
    protected $communicationConfig;

    /**
     * @var ServiceMethodsMap
     */
    private $serviceMethodsMap;

    /**
     * @var CommunicationRemoteServiceReader
     */
    private $communicationRemoteServiceReader;

    /**
     * Initialize dependencies.
     *
     * @param CommunicationConfig $communicationConfig
     * @param ServiceMethodsMap $serviceMethodsMap
     * @param CommunicationRemoteServiceReader $communicationRemoteServiceReader
     * @param string|null $sourceClassName
     * @param string|null $resultClassName
     * @param Io $ioObject
     * @param \Magento\Framework\Code\Generator\CodeGeneratorInterface $classGenerator
     * @param DefinedClasses $definedClasses
     */
    public function __construct(
        CommunicationConfig $communicationConfig,
        ServiceMethodsMap $serviceMethodsMap,
        CommunicationRemoteServiceReader $communicationRemoteServiceReader,
        $sourceClassName = null,
        $resultClassName = null,
        Io $ioObject = null,
        \Magento\Framework\Code\Generator\CodeGeneratorInterface $classGenerator = null,
        DefinedClasses $definedClasses = null
    ) {
        $this->communicationConfig = $communicationConfig;
        $this->serviceMethodsMap = $serviceMethodsMap;
        $this->communicationRemoteServiceReader = $communicationRemoteServiceReader;
        parent::__construct(
            $sourceClassName,
            $resultClassName,
            $ioObject,
            $classGenerator,
            $definedClasses
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function _getDefaultConstructorDefinition()
    {
        return [
            'name' => '__construct',
            'parameters' => [
                ['name' => 'publisherPool', 'type' => '\Magento\Framework\MessageQueue\PublisherPool'],
            ],
            'body' => "\$this->publisherPool = \$publisherPool;",
            'docblock' => [
                'shortDescription' => 'Initialize dependencies.',
                'tags' => [
                    [
                        'name' => 'param',
                        'description' => '\Magento\Framework\MessageQueue\PublisherPool $publisherPool',
                    ],
                ],
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function _getClassProperties()
    {
        return [
            [
                'name' => 'publisherPool',
                'visibility' => 'protected',
                'docblock' => [
                    'shortDescription' => 'Publisher pool',
                    'tags' => [['name' => 'var', 'description' => '\Magento\Framework\MessageQueue\PublisherPool']],
                ],
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function _getClassMethods()
    {
        $methods = [$this->_getDefaultConstructorDefinition()];
        $interfaceMethodsMap = $this->serviceMethodsMap->getMethodsMap($this->getSourceClassName());
        foreach (array_keys($interfaceMethodsMap) as $methodName) {
            $sourceMethodParameters = $this->serviceMethodsMap->getMethodParams(
                $this->getSourceClassName(),
                $methodName
            );
            $methodParameters = [];
            $topicParameters = [];
            foreach ($sourceMethodParameters as $methodParameter) {
                $parameterName = $methodParameter[ServiceMethodsMap::METHOD_META_NAME];
                $parameter = [
                    'name' => $parameterName,
                ];
                if ($methodParameter[ServiceMethodsMap::METHOD_META_TYPE] === 'array') {
                    $parameter['type'] = 'array';
                } else {
                    $fullyQualifiedTypeName = '\\' . ltrim($methodParameter[ServiceMethodsMap::METHOD_META_TYPE], '\\');
                    if (interface_exists($fullyQualifiedTypeName) || class_exists($fullyQualifiedTypeName)) {
                        $parameter['type'] = $fullyQualifiedTypeName;
                    }
                }
                if ($methodParameter[ServiceMethodsMap::METHOD_META_HAS_DEFAULT_VALUE]) {
                    $parameter['defaultValue'] = $methodParameter[ServiceMethodsMap::METHOD_META_DEFAULT_VALUE] !== null
                    ? $methodParameter[ServiceMethodsMap::METHOD_META_DEFAULT_VALUE]
                    : $this->_getNullDefaultValue();
                }
                $methodParameters[] = $parameter;
                $topicParameters[] = "'{$parameterName}' => \${$parameterName}";
            }
            $topicName = $this->communicationRemoteServiceReader->generateTopicName(
                $this->getSourceClassName(),
                $methodName
            );
            $topicConfig = $this->communicationConfig->getTopic($topicName);
            $methodBody = $topicConfig[CommunicationConfig::TOPIC_IS_SYNCHRONOUS] ? 'return ' : '';
            $methodBody .= "\$this->publisherPool\n"
                . "    ->getByTopicType('{$topicName}')\n"
                . "    ->publish(\n"
                . "        '{$topicName}',\n"
                . "        [" . implode(', ', $topicParameters) . "]\n"
                . "    );";
            $annotations = [['name' => 'inheritdoc']];
            $methods[] = [
                'name' => $methodName,
                'parameters' => $methodParameters,
                'body' => $methodBody,
                'docblock' => ['tags' => $annotations]
            ];
        }
        return $methods;
    }

    /**
     * {@inheritdoc}
     */
    protected function _validateData()
    {
        $classNameValidationResults = $this->validateResultClassName();
        return parent::_validateData() && $classNameValidationResults;
    }

    /**
     * {@inheritdoc}
     */
    protected function _generateCode()
    {
        $this->_classGenerator->setImplementedInterfaces([$this->getSourceClassName()]);
        return parent::_generateCode();
    }

    /**
     * Ensure that result class name corresponds to the source class name.
     *
     * @return bool
     */
    protected function validateResultClassName()
    {
        $result = true;
        $sourceClassName = $this->getSourceClassName();
        $resultClassName = $this->_getResultClassName();
        $interfaceSuffix = 'Interface';
        if (substr($sourceClassName, -strlen($interfaceSuffix)) !== $interfaceSuffix) {
            $this->_addError(
                sprintf(
                    'Remote service class "%s" should be set as preference for an interface, "%s" given',
                    $resultClassName,
                    $sourceClassName
                )
            );
        }
        $expectedResultClassName = $sourceClassName . self::REMOTE_SERVICE_SUFFIX;
        if ($resultClassName !== $expectedResultClassName) {
            $this->_addError(
                'Invalid remote service class name [' . $resultClassName . ']. Use ' . $expectedResultClassName
            );
            $result = false;
        }
        return $result;
    }
}
