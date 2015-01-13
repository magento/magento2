<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
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
interface InterfaceFirst
{
}
class ImplementationOfInterfaceFirst implements InterfaceFirst
{
}
interface InterfaceSecond
{
}
class ImplementationOfInterfaceSecond implements InterfaceSecond
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
     * @var InterfaceFirst
     */
    protected $_interfaceA;

    /**
     * @var ImplementationOfInterfaceSecond
     */
    protected $_implOfBInterface;

    /**
     * @param ClassFirst $exA
     * @param ClassSecond $exB
     * @param ClassThird $exC
     * @param InterfaceFirst $interfaceA
     * @param ImplementationOfInterfaceSecond $implOfBInterface
     */
    public function __construct(
        \ClassFirst $exA,
        \ClassSecond $exB,
        \ClassThird $exC,
        \InterfaceFirst $interfaceA,
        \ImplementationOfInterfaceSecond $implOfBInterface
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
     * @var ImplementationOfInterfaceFirst
     */
    protected $_exA;

    /**
     * @param ContextFirst $context
     * @param ImplementationOfInterfaceFirst $exA
     */
    public function __construct(\ContextFirst $context, \ImplementationOfInterfaceFirst $exA)
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
     * @var InterfaceSecond
     */
    protected $_exB;

    /**
     * @param ContextFirst $context
     * @param InterfaceSecond $exB
     */
    public function __construct(\ContextFirst $context, \InterfaceSecond $exB)
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
     * @var InterfaceFirst
     */
    protected $_exA;

    /**
     * @param ContextFirst $context
     * @param InterfaceFirst $exA
     */
    public function __construct(\ContextFirst $context, \InterfaceFirst $exA)
    {
        $this->_context = $context;
        $this->_exA = $exA;
    }
}
