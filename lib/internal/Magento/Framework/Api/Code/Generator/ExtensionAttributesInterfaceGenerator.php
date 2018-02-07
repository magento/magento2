<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\Code\Generator;

use Magento\Framework\Code\Generator\DefinedClasses;
use Magento\Framework\Code\Generator\Io;

/**
 * Code generator for data object extension interfaces.
 */
class ExtensionAttributesInterfaceGenerator extends \Magento\Framework\Api\Code\Generator\ExtensionAttributesGenerator
{
    const ENTITY_TYPE = 'extensionInterface';

    const EXTENSION_INTERFACE_SUFFIX = 'ExtensionInterface';

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Api\ExtensionAttribute\Config $config
     * @param string|null $sourceClassName
     * @param string|null $resultClassName
     * @param Io $ioObject
     * @param \Magento\Framework\Code\Generator\CodeGeneratorInterface $classGenerator
     * @param DefinedClasses $definedClasses
     */
    public function __construct(
        \Magento\Framework\Api\ExtensionAttribute\Config $config,
        $sourceClassName = null,
        $resultClassName = null,
        Io $ioObject = null,
        \Magento\Framework\Code\Generator\CodeGeneratorInterface $classGenerator = null,
        DefinedClasses $definedClasses = null
    ) {
        if (!$classGenerator) {
            $classGenerator = new \Magento\Framework\Code\Generator\InterfaceGenerator();
        }
        parent::__construct(
            $config,
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
    protected function getExtendedClass()
    {
        return '\Magento\Framework\Api\ExtensionAttributesInterface';
    }

    /**
     * {@inheritdoc}
     */
    protected function validateResultClassName()
    {
        $result = true;
        $sourceClassName = $this->getSourceClassName();
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
        return $result;
    }
}
