<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\Code\Generator;

use Magento\Framework\Code\Generator\DefinedClasses;
use Magento\Framework\Code\Generator\Io;
use Magento\Framework\Api\SimpleDataObjectConverter;

/**
 * Code generator for data object extension interfaces.
 */
class ObjectExtensionInterface extends \Magento\Framework\Code\Generator\EntityAbstract
{
    const ENTITY_TYPE = 'extensionInterface';

    const EXTENSION_INTERFACE_SUFFIX = 'ExtensionInterface';

    /**
     * @var \Magento\Framework\Api\Config\Reader
     */
    protected $configReader;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Api\Config\Reader $configReader
     * @param string|null $sourceClassName
     * @param string|null $resultClassName
     * @param Io $ioObject
     * @param \Magento\Framework\Code\Generator\CodeGeneratorInterface $classGenerator
     * @param DefinedClasses $definedClasses
     */
    public function __construct(
        \Magento\Framework\Api\Config\Reader $configReader,
        $sourceClassName = null,
        $resultClassName = null,
        Io $ioObject = null,
        \Magento\Framework\Code\Generator\CodeGeneratorInterface $classGenerator = null,
        DefinedClasses $definedClasses = null
    ) {
        if (!$classGenerator) {
            $classGenerator = new \Magento\Framework\Code\Generator\InterfaceGenerator();
        }
        $sourceClassName .= 'Interface';
        $this->configReader = $configReader;
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
        return [];
    }

    /**
     * Returns list of methods for class generator
     *
     * @return array
     */
    protected function _getClassMethods()
    {
        $customAttributes = $this->configReader->read();
        $dataInterface = ltrim($this->_getSourceClassName(), '\\');
        $methods = [];
        if (isset($customAttributes[$dataInterface])) {
            foreach ($customAttributes[$dataInterface] as $attributeName => $attributeType) {
                $methodName = 'get' . SimpleDataObjectConverter::snakeCaseToUpperCamelCase($attributeName);
                $methods[] = [
                    'name' => $methodName,
                    'docblock' => ['tags' => [['name' => 'return', 'description' => $attributeType]]],
                ];
            }
        }
        return $methods;
    }

    /**
     * {@inheritdoc}
     */
    protected function _validateData()
    {
        $result = true;
        $sourceClassName = $this->_getSourceClassName();
        $resultClassName = $this->_getResultClassName();
        $interfaceSuffix = 'Interface';
        $expectedResultClassName = substr($sourceClassName, 0, -strlen($interfaceSuffix))
            . self::EXTENSION_INTERFACE_SUFFIX;
        if ($resultClassName !== $expectedResultClassName) {
            $this->_addError(
                'Invalid extension interface name [' . $resultClassName . ']. Use ' . $expectedResultClassName
            );
            $result = false;
        }
        return parent::_validateData() && $result;
    }
}
