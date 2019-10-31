<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Dto\Code\Generator;

use Magento\Framework\Code\Generator\CodeGeneratorInterface;
use Magento\Framework\Code\Generator\DefinedClasses;
use Magento\Framework\Code\Generator\Io;
use Magento\Framework\Dto\Code\GetMutatorSourceCode;
use Magento\Framework\Code\Generator\EntityAbstract;
use ReflectionException;

/**
 * Class Mutator
 */
class Mutator extends EntityAbstract
{
    /**
     * Entity type
     */
    public const ENTITY_TYPE = 'mutator';

    /**
     * @var GetMutatorSourceCode|null
     */
    private $getMutatorSourceCode;

    /**
     * @param null $sourceClassName
     * @param null $resultClassName
     * @param Io|null $ioObject
     * @param CodeGeneratorInterface|null $classGenerator
     * @param DefinedClasses|null $definedClasses
     * @param GetMutatorSourceCode|null $getMutatorSourceCode
     */
    public function __construct(
        $sourceClassName = null,
        $resultClassName = null,
        Io $ioObject = null,
        CodeGeneratorInterface $classGenerator = null,
        DefinedClasses $definedClasses = null,
        GetMutatorSourceCode $getMutatorSourceCode = null
    ) {
        parent::__construct(
            $sourceClassName,
            $resultClassName,
            $ioObject,
            $classGenerator,
            $definedClasses
        );

        $this->getMutatorSourceCode = $getMutatorSourceCode ?: new GetMutatorSourceCode();
    }

    /**
     * Get default constructor definition for generated class
     *
     * @return array
     */
    protected function _getDefaultConstructorDefinition()
    {
        return []; // See \Magento\Framework\Dto\Code\GetMutatorSourceCode
    }

    /**
     * Returns list of methods for class generator
     *
     * @return array
     */
    protected function _getClassMethods()
    {
        return []; // See \Magento\Framework\Dto\Code\GetMutatorSourceCode
    }

    /**
     * @return string
     * @throws ReflectionException
     */
    protected function _getGeneratedCode()
    {
        return $this->getMutatorSourceCode->execute(
            $this->getSourceClassName(),
            $this->_getResultClassName()
        );
    }
}
