<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\Code\Generator;

use Magento\Framework\Code\Generator\DefinedClasses;
use Magento\Framework\Code\Generator\Io;
use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\Api\ExtensionAttribute\Config\Converter;

/**
 * Code generator for data object extensions.
 * @since 2.0.0
 */
class ExtensionAttributesGenerator extends \Magento\Framework\Code\Generator\EntityAbstract
{
    const ENTITY_TYPE = 'extension';

    const EXTENSION_SUFFIX = 'Extension';

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\Config
     * @since 2.0.0
     */
    protected $config;

    /**
     * @var \Magento\Framework\Reflection\TypeProcessor
     * @since 2.1.0
     */
    private $typeProcessor;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $allCustomAttributes;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Api\ExtensionAttribute\Config $config
     * @param string|null $sourceClassName
     * @param string|null $resultClassName
     * @param Io $ioObject
     * @param \Magento\Framework\Code\Generator\CodeGeneratorInterface $classGenerator
     * @param DefinedClasses $definedClasses
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Api\ExtensionAttribute\Config $config,
        $sourceClassName = null,
        $resultClassName = null,
        Io $ioObject = null,
        \Magento\Framework\Code\Generator\CodeGeneratorInterface $classGenerator = null,
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
     * @return \Magento\Framework\Reflection\TypeProcessor
     * @deprecated 2.1.0
     * @since 2.1.0
     */
    private function getTypeProcessor()
    {
        if ($this->typeProcessor === null) {
            $this->typeProcessor = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Reflection\TypeProcessor::class
            );
        }
        return $this->typeProcessor;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function _getDefaultConstructorDefinition()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function _getClassProperties()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function _getClassMethods()
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

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function _validateData()
    {
        $classNameValidationResults = $this->validateResultClassName();
        return parent::_validateData() && $classNameValidationResults;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function getExtendedClass()
    {
        return '\\' . \Magento\Framework\Api\AbstractSimpleObject::class;
    }

    /**
     * Retrieve a list of attributes associated with current source class.
     *
     * @return array
     * @since 2.0.0
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
        } else {
            return [];
        }
    }

    /**
     * Ensure that result class name corresponds to the source class name.
     *
     * @return bool
     * @since 2.0.0
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
