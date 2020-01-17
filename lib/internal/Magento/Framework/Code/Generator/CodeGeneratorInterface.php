<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code\Generator;

/**
 * Interface \Magento\Framework\Code\Generator\CodeGeneratorInterface
 *
 */
interface CodeGeneratorInterface extends \Laminas\Code\Generator\GeneratorInterface
{
    /**
     * Set class name.
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Set class doc block.
     *
     * @param array $docBlock
     * @return $this
     */
    public function setClassDocBlock(array $docBlock);

    /**
     * Add a list of properties.
     *
     * @param array $properties
     * @return $this
     */
    public function addProperties(array $properties);

    /**
     * Add a list of methods.
     *
     * @param array $methods
     * @return $this
     */
    public function addMethods(array $methods);

    /**
     * Set extended class.
     *
     * @param string $extendedClass
     * @return $this
     */
    public function setExtendedClass($extendedClass);

    /**
     * Set a list of implemented interfaces.
     *
     * @param array $interfaces
     * @return $this
     */
    public function setImplementedInterfaces(array $interfaces);

    /**
     * Add a trait to the class.
     *
     * @param string $trait
     * @return $this
     */
    public function addTrait($trait);
}
