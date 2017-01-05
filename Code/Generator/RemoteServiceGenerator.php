<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Code\Generator;

use Magento\Framework\Code\Generator\DefinedClasses;
use Magento\Framework\Code\Generator\Io;
use Magento\Framework\Communication\Config\ReflectionGenerator;
use Magento\Framework\Communication\ConfigInterface as CommunicationConfig;
use Magento\Framework\MessageQueue\Code\Generator\Config\RemoteServiceReader\Communication as RemoteServiceReader;
use Magento\Framework\Reflection\MethodsMap as ServiceMethodsMap;

/**
 * Code generator for remote services.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @var ReflectionGenerator
     */
    private $reflectionGenerator;

    /**
     * Initialize dependencies.
     *
     * @param CommunicationConfig $communicationConfig
     * @param ServiceMethodsMap $serviceMethodsMap
     * @param RemoteServiceReader $communicationRemoteServiceReader
     * @param string|null $sourceClassName
     * @param string|null $resultClassName
     * @param Io $ioObject
     * @param \Magento\Framework\Code\Generator\CodeGeneratorInterface $classGenerator
     * @param DefinedClasses $definedClasses
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        CommunicationConfig $communicationConfig,
        ServiceMethodsMap $serviceMethodsMap,
        RemoteServiceReader $communicationRemoteServiceReader,
        $sourceClassName = null,
        $resultClassName = null,
        Io $ioObject = null,
        \Magento\Framework\Code\Generator\CodeGeneratorInterface $classGenerator = null,
        DefinedClasses $definedClasses = null
    ) {
        $this->communicationConfig = $communicationConfig;
        $this->serviceMethodsMap = $serviceMethodsMap;
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
                ['name' => 'publisher', 'type' => '\\' . \Magento\Framework\MessageQueue\PublisherInterface::class],
            ],
            'body' => "\$this->publisher = \$publisher;",
            'docblock' => [
                'shortDescription' => 'Initialize dependencies.',
                'tags' => [
                    [
                        'name' => 'param',
                        'description' => '\Magento\Framework\MessageQueue\PublisherInterface $publisher',
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
                'name' => 'publisher',
                'visibility' => 'protected',
                'docblock' => [
                    'shortDescription' => 'Publisher',
                    'tags' => [
                        [
                            'name' => 'var',
                            'description' => '\\' . \Magento\Framework\MessageQueue\PublisherInterface::class
                        ]
                    ],
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
            $topicName = $this->getReflectionGenerator()->generateTopicName($this->getSourceClassName(), $methodName);
            $topicConfig = $this->communicationConfig->getTopic($topicName);
            $methodBody = $topicConfig[CommunicationConfig::TOPIC_IS_SYNCHRONOUS] ? 'return ' : '';
            $methodBody .= "\$this->publisher->publish(\n"
                . "    '{$topicName}',\n"
                . "    [" . implode(', ', $topicParameters) . "]\n"
                . ");";
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

    /**
     * Get reflection generator.
     *
     * @return ReflectionGenerator
     *
     * @deprecated
     */
    private function getReflectionGenerator()
    {
        if ($this->reflectionGenerator === null) {
            $this->reflectionGenerator = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(ReflectionGenerator::class);
        }
        return $this->reflectionGenerator;
    }
}
