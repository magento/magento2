<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @codingStandardsIgnoreFile
 * Coding Standards have to be ignored in this file, as it is just a data source for tests.
 */

class ClassA
{
}
class ClassB
{
}
class ClassC
{
}
interface FirstInterface
{
}
class ImplementationOfFirstInterface implements FirstInterface
{
}
interface SecondInterface
{
}
class ImplementationOfSecondInterface implements SecondInterface
{
}
class Context implements \Magento\Framework\ObjectManager\ContextInterface
{
    /**
     * @var ClassA
     */
    protected $_exA;

    /**
     * @var ClassB
     */
    protected $_exB;

    /**
     * @var ClassC
     */
    protected $_exC;

    /**
     * @var FirstInterface
     */
    protected $_interfaceA;

    /**
     * @var ImplementationOfSecondInterface
     */
    protected $_implOfBInterface;

    public function __construct(
        \ClassA $exA,
        \ClassB $exB,
        \ClassC $exC,
        \FirstInterface $interfaceA,
        \ImplementationOfSecondInterface $implOfBInterface
    ) {
        $this->_exA = $exA;
        $this->_exB = $exB;
        $this->_exC = $exC;
        $this->_interfaceA = $interfaceA;
        $this->_implOfBInterface = $implOfBInterface;
    }
}
class ClassArgumentAlreadyInjectedIntoContext
{
    /**
     * @var Context
     */
    protected $_context;

    /**
     * @var ClassA
     */
    protected $_exA;

    /**
     * @param Context $context
     * @param ClassA $exA
     */
    public function __construct(\Context $context, \ClassA $exA)
    {
        $this->_context = $context;
        $this->_exA = $exA;
    }
}
class ClassArgumentWrongOrderForParentArguments extends ClassArgumentAlreadyInjectedIntoContext
{
    /**
     * @var Context
     */
    protected $_context;

    /**
     * @var ClassA
     */
    protected $_exA;

    /**
     * @var ClassB
     */
    protected $_exB;

    /**
     * @param Context $context
     * @param ClassA $exA
     * @param ClassB $exB
     */
    public function __construct(\Context $context, \ClassA $exA, \ClassB $exB)
    {
        parent::__construct($exA, $context);
        $this->_context = $context;
        $this->_exA = $exA;
        $this->_exB = $exB;
    }
}
class ClassArgumentWithOptionalParams
{
    /**
     * @var Context
     */
    protected $_context;

    /**
     * @var array
     */
    protected $_data;

    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(\Context $context, array $data = [])
    {
        $this->_context = $context;
        $this->_data = $data;
    }
}
class ClassArgumentWithWrongParentArgumentsType extends ClassArgumentWithOptionalParams
{
    /**
     * @var Context
     */
    protected $_context;

    /**
     * @var ClassB
     */
    protected $_exB;

    /**
     * @var ClassC
     */
    protected $_exC;

    /**
     * @var array
     */
    protected $_data;

    /**
     * @param Context $context
     * @param ClassB $exB
     * @param ClassC $exC
     * @param array $data
     */
    public function __construct(\Context $context, \ClassB $exB, \ClassC $exC, array $data = [])
    {
        parent::__construct($context, $exB);
        $this->_context = $context;
        $this->_exB = $exB;
        $this->_exC = $exC;
        $this->_data = $data;
    }
}
