<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

class ClassFirst
{
}
class ClassSecond
{
}
class ClassThird
{
}
class ClassD
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
class ContextFirst implements \Magento\Framework\ObjectManager\ContextInterface
{
    /**
     * @var ClassFirst
     */
    protected $_exA;

    /**
     * @var ClassSecond
     */
    protected $_exB;

    /**
     * @var ClassThird
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

    /**
     * @param ClassFirst $exA
     * @param ClassSecond $exB
     * @param ClassThird $exC
     * @param FirstInterface $interfaceA
     * @param ImplementationOfSecondInterface $implOfBInterface
     */
    public function __construct(
        \ClassFirst $exA,
        \ClassSecond $exB,
        \ClassThird $exC,
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
class ClassArgumentAlreadyInjectedInContext
{
    /**
     * @var ContextFirst
     */
    protected $_context;

    /**
     * @var ClassFirst
     */
    protected $_exA;

    /**
     * @param ContextFirst $context
     * @param ClassFirst $exA
     */
    public function __construct(\ContextFirst $context, \ClassFirst $exA)
    {
        $this->_context = $context;
        $this->_exA = $exA;
    }
}
class ClassArgumentWithInterfaceImplementation
{
    /**
     * @var ContextFirst
     */
    protected $_context;

    /**
     * @var ImplementationOfFirstInterface
     */
    protected $_exA;

    /**
     * @param ContextFirst $context
     * @param ImplementationOfFirstInterface $exA
     */
    public function __construct(\ContextFirst $context, \ImplementationOfFirstInterface $exA)
    {
        $this->_context = $context;
        $this->_exA = $exA;
    }
}
class ClassArgumentWithInterface
{
    /**
     * @var ContextFirst
     */
    protected $_context;

    /**
     * @var SecondInterface
     */
    protected $_exB;

    /**
     * @param ContextFirst $context
     * @param SecondInterface $exB
     */
    public function __construct(\ContextFirst $context, \SecondInterface $exB)
    {
        $this->_context = $context;
        $this->_exB = $exB;
    }
}
class ClassArgumentWithAlreadyInjectedInterface
{
    /**
     * @var ContextFirst
     */
    protected $_context;

    /**
     * @var FirstInterface
     */
    protected $_exA;

    /**
     * @param ContextFirst $context
     * @param FirstInterface $exA
     */
    public function __construct(\ContextFirst $context, \FirstInterface $exA)
    {
        $this->_context = $context;
        $this->_exA = $exA;
    }
}
