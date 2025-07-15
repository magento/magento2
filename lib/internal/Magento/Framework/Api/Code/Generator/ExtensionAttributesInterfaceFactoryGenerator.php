<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api\Code\Generator;

use Magento\Framework\Code\Generator\CodeGeneratorInterface;
use Magento\Framework\Code\Generator\DefinedClasses;
use Magento\Framework\Code\Generator\Io;
use Magento\Framework\ObjectManager\Code\Generator\Factory;

class ExtensionAttributesInterfaceFactoryGenerator extends Factory
{
    /**
     * {@inheritdoc}
     */
    const ENTITY_TYPE = 'extensionInterfaceFactory';

    /**
     * Initialize dependencies.
     *
     * @param string|null $sourceClassName
     * @param string|null $resultClassName
     * @param Io $ioObject
     * @param CodeGeneratorInterface $classGenerator
     * @param DefinedClasses $definedClasses
     */
    public function __construct(
        $sourceClassName = null,
        $resultClassName = null,
        ?Io $ioObject = null,
        ?CodeGeneratorInterface $classGenerator = null,
        ?DefinedClasses $definedClasses = null
    ) {
        $sourceClassName .= 'Extension';
        parent::__construct(
            $sourceClassName,
            $resultClassName,
            $ioObject,
            $classGenerator,
            $definedClasses
        );
    }

    /**
     * @inheritdoc
     */
    protected function getResultClassSuffix()
    {
        return 'InterfaceFactory';
    }
}
