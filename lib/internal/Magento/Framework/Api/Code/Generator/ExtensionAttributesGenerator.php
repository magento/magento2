<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\Code\Generator;

use Magento\Framework\Api\AbstractImmutableSimpleObject;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Api\ExtensionAttribute\Config;
use Magento\Framework\Api\ImmutableExtensibleDataInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Code\Generator\CodeGeneratorInterface;
use Magento\Framework\Code\Generator\DefinedClasses;
use Magento\Framework\Code\Generator\EntityAbstract;
use Magento\Framework\Code\Generator\Io;
use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\Api\ExtensionAttribute\Config\Converter;
use Magento\Framework\Reflection\TypeProcessor;
use ReflectionClass;
use Zend\Code\Generator\ValueGenerator;

/**
 * Code generator for data object extensions.
 */
class ExtensionAttributesGenerator extends EntityAbstract
{
    const ENTITY_TYPE = 'extension';

    const EXTENSION_SUFFIX = 'Extension';

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var TypeProcessor
     */
    private $typeProcessor;

    /**
     * @var array
     */
    protected $allCustomAttributes;

    /**
     * Initialize dependencies.
     *
     * @param Config $config
     * @param string|null $sourceClassName
     * @param string|null $resultClassName
     * @param Io $ioObject
     * @param CodeGeneratorInterface $classGenerator
     * @param DefinedClasses $definedClasses
     */
    public function __construct(
        Config $config,
        $sourceClassName = null,
        $resultClassName = null,
        Io $ioObject = null,
        CodeGeneratorInterface $classGenerator = null,
        DefinedClasses $definedClasses = null
    ) {
        $sourceClassName .= 'Interface';
        $this->config = $config;
        parent::__construct(
            $sourceClassName,
            $resultClassName,
            $ioObject,
            $classGenerator,
            $definedClasses
        );
    }

    /**
     * Get type processor
     *
     * @return TypeProcessor
     * @deprecated 100.1.0
     */
    private function getTypeProcessor()
    {
        if ($this->typeProcessor === null) {
            $this->typeProcessor = ObjectManager::getInstance()->get(
                TypeProcessor::class
            );
        }
        return $this->typeProcessor;
    }
    /**
     * @inheritDoc
     */
    protected function _getClassProperties()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    protected function _getDefaultConstructorDefinition()
    {
        return [];
    }

    /**
     * @return array
     */
    private function getImmutableClassMethods(): array
    {
        $methods = [];

        foreach ($this->getCustomAttributes() as $attributeName => $attributeMetadata) {
            $attributeType = $attributeMetadata[Converter::DATA_TYPE];
            $propertyName = SimpleDataObjectConverter::snakeCaseToCamelCase($attributeName);
            $getterName = 'get' . ucfirst($propertyName);

            $methods[] = [
                'name' => $getterName,
                'body' => "return \$this->get('{$attributeName}');",
                'docblock' => ['tags' => [['name' => 'return', 'description' => $attributeType . '|null']]],
                'returnType' => '?' . $attributeType
            ];
        }

        return $methods;
    }

    /**
     * @return array
     */
    private function getMutableClassMethods(): array
    {
        $methods = [];
        foreach ($this->getCustomAttributes() as $attributeName => $attributeMetadata) {
            $attributeType = $attributeMetadata[Converter::DATA_TYPE];
            $propertyName = SimpleDataObjectConverter::snakeCaseToCamelCase($attributeName);
            $getterName = 'get' . ucfirst($propertyName);
            $setterName = 'set' . ucfirst($propertyName);
            $methods[] = [
                'name' => $getterName,
                'body' => "return \$this->_get('{$attributeName}');",
                'docblock' => ['tags' => [['name' => 'return', 'description' => $attributeType . '|null']]],
            ];
            $parameters = ['name' => $propertyName];
            // If the attribute type is a valid type declaration (e.g., interface, class, array) then use it to enforce
            // constraints on the generated setter methods
            if ($this->getTypeProcessor()->isValidTypeDeclaration($attributeType)) {
                $parameters['type'] = $attributeType;
            }
            $methods[] = [
                'name' => $setterName,
                'parameters' => [$parameters],
                'body' => "\$this->setData('{$attributeName}', \${$propertyName});" . PHP_EOL . "return \$this;",
                'docblock' => [
                    'tags' => [
                        [
                            'name' => 'param',
                            'description' => "{$attributeType} \${$propertyName}"
                        ],
                        [
                            'name' => 'return',
                            'description' => '$this'
                        ]
                    ]
                ],
            ];
        }

        return $methods;
    }

    private function isImmutable(): bool
    {
        $typeName = $this->getSourceClassName();
        $reflection = new ReflectionClass($typeName);
        return $reflection->implementsInterface(ImmutableExtensibleDataInterface::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function _getClassMethods()
    {
        return $this->isImmutable() ? $this->getImmutableClassMethods() : $this->getMutableClassMethods();
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
        $this->_classGenerator->setImplementedInterfaces([$this->_getResultClassName() . 'Interface']);
        $this->_classGenerator->setExtendedClass($this->getExtendedClass());

        return parent::_generateCode();
    }

    /**
     * Get class, which should be used as a parent for generated class.
     *
     * @return string
     */
    protected function getExtendedClass()
    {
        return '\\' . ($this->isImmutable() ? AbstractImmutableSimpleObject::class : AbstractSimpleObject::class);
    }

    /**
     * Retrieve a list of attributes associated with current source class.
     *
     * @return array
     */
    protected function getCustomAttributes()
    {
        if (!isset($this->allCustomAttributes)) {
            $this->allCustomAttributes = $this->config->get();
        }
        $dataInterface = ltrim($this->getSourceClassName(), '\\');
        if (isset($this->allCustomAttributes[$dataInterface])) {
            foreach ($this->allCustomAttributes[$dataInterface] as $attributeName => $attributeMetadata) {
                $attributeType = $attributeMetadata[Converter::DATA_TYPE];
                if (strpos($attributeType, '\\') !== false) {
                    /** Add preceding slash to class names, while leaving primitive types as is */
                    $attributeType = $this->_getFullyQualifiedClassName($attributeType);
                    $this->allCustomAttributes[$dataInterface][$attributeName][Converter::DATA_TYPE] =
                        $this->_getFullyQualifiedClassName($attributeType);
                }
            }
            return $this->allCustomAttributes[$dataInterface];
        }

        return [];
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
        $expectedResultClassName = substr($sourceClassName, 0, -strlen($interfaceSuffix)) . self::EXTENSION_SUFFIX;
        if ($resultClassName !== $expectedResultClassName) {
            $this->_addError(
                'Invalid extension name [' . $resultClassName . ']. Use ' . $expectedResultClassName
            );
            $result = false;
        }
        return $result;
    }
}
