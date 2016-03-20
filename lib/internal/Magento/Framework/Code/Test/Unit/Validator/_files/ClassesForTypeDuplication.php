<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace TypeDuplication;

interface ArgumentInterface
{
}
class ArgumentBaseClass
{
}
class ArgumentClassOne extends ArgumentBaseClass
{
}
class ValidClassWithTheSameInterfaceTypeArguments
{
    /**
     * @var ArgumentInterface
     */
    protected $argumentOne;

    /**
     * @var ArgumentClassOne
     */
    protected $argumentTwo;

    /**
     * @var ArgumentInterface
     */
    protected $argumentThree;

    /**
     * @param ArgumentInterface $argumentOne
     * @param ArgumentClassOne $argumentTwo
     * @param ArgumentInterface $argumentThree
     */
    public function __construct(
        ArgumentInterface $argumentOne,
        ArgumentClassOne $argumentTwo,
        ArgumentInterface $argumentThree
    ) {
        $this->argumentOne = $argumentOne;
        $this->argumentTwo = $argumentTwo;
        $this->argumentThree = $argumentThree;
    }
}
class ValidClassWithSubTypeArguments
{
    /**
     * @var ArgumentBaseClass
     */
    protected $argumentOne;

    /**
     * @var ArgumentClassOne
     */
    protected $argumentTwo;

    /**
     * @var ArgumentInterface
     */
    protected $argumentThree;

    /**
     * @param ArgumentBaseClass $argumentOne
     * @param ArgumentClassOne $argumentTwo
     * @param ArgumentInterface $argumentThree
     */
    public function __construct(
        ArgumentBaseClass $argumentOne,
        ArgumentClassOne $argumentTwo,
        ArgumentInterface $argumentThree
    ) {
        $this->argumentOne = $argumentOne;
        $this->argumentTwo = $argumentTwo;
        $this->argumentThree = $argumentThree;
    }
}
class ValidClassWithSuppressWarnings
{
    /**
     * @var ArgumentBaseClass
     */
    protected $argumentOne;

    /**
     * @var ArgumentBaseClass
     */
    protected $argumentTwo;

    /**
     * @var ArgumentInterface
     */
    protected $argumentThree;

    /**
     * @param ArgumentBaseClass $argumentOne
     * @param ArgumentBaseClass $argumentTwo
     * @param ArgumentInterface $argumentThree
     *
     * @SuppressWarnings(Magento.TypeDuplication)
     */
    public function __construct(
        ArgumentBaseClass $argumentOne,
        ArgumentBaseClass $argumentTwo,
        ArgumentInterface $argumentThree
    ) {
        $this->argumentOne = $argumentOne;
        $this->argumentTwo = $argumentTwo;
        $this->argumentThree = $argumentThree;
    }
}
class InvalidClassWithDuplicatedTypes
{
    /**
     * @var ArgumentBaseClass
     */
    protected $argumentOne;

    /**
     * @var ArgumentBaseClass
     */
    protected $argumentTwo;

    /**
     * @var ArgumentInterface
     */
    protected $argumentThree;

    /**
     * @param ArgumentBaseClass $argumentOne
     * @param ArgumentBaseClass $argumentTwo
     * @param ArgumentInterface $argumentThree
     */
    public function __construct(
        ArgumentBaseClass $argumentOne,
        ArgumentBaseClass $argumentTwo,
        ArgumentInterface $argumentThree
    ) {
        $this->argumentOne = $argumentOne;
        $this->argumentTwo = $argumentTwo;
        $this->argumentThree = $argumentThree;
    }
}
