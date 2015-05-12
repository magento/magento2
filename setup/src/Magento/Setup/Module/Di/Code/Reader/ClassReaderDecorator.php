<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Module\Di\Code\Reader;

use Magento\Setup\Module\Di\Compiler\ConstructorArgument;

class ClassReaderDecorator implements \Magento\Framework\Code\Reader\ClassReaderInterface
{
    /**
     * @var \Magento\Framework\Code\Reader\ClassReader
     */
    private $classReader;

    /**
     * @param \Magento\Framework\Code\Reader\ClassReader $classReader
     */
    public function __construct(\Magento\Framework\Code\Reader\ClassReader $classReader)
    {
        $this->classReader = $classReader;
    }

    /**
     * Read class constructor signature
     *
     * @param string $className
     * @return ConstructorArgument[]|null
     * @throws \ReflectionException
     */
    public function getConstructor($className)
    {
        $unmappedArguments = $this->classReader->getConstructor($className);
        if ($unmappedArguments === null) {
            return $unmappedArguments;
        }

        $arguments = [];
        foreach ($unmappedArguments as $argument) {
            $arguments[] = new ConstructorArgument($argument);
        }

        return $arguments;
    }

    /**
     * Retrieve parent relation information for type in a following format
     * array(
     *     'Parent_Class_Name',
     *     'Interface_1',
     *     'Interface_2',
     *     ...
     * )
     *
     * @param string $className
     * @return string[]
     */
    public function getParents($className)
    {
        return $this->classReader->getParents($className);
    }
}
