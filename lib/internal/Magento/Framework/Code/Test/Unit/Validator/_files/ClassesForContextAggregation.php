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
interface ThirdInterface
{
}
class ImplementationOfThirdInterface implements ThirdInterface
{
}
interface FourthInterface
{
}
class ImplementationOfFourthInterface implements FourthInterface
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
     * @var ThirdInterface
     */
    protected $_interfaceA;

    /**
     * @var ImplementationOfFourthInterface
     */
    protected $_implOfBInterface;

    /**
     * @param ClassFirst $exA
     * @param ClassSecond $exB
     * @param ClassThird $exC
     * @param ThirdInterface $interfaceA
     * @param ImplementationOfFourthInterface $implOfBInterface
     */
    public function __construct(
        \ClassFirst $exA,
        \ClassSecond $exB,
        \ClassThird $exC,
        \ThirdInterface $interfaceA,
        \ImplementationOfFourthInterface $implOfBInterface
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
     * @var ImplementationOfThirdInterface
     */
    protected $_exA;

    /**
     * @param ContextFirst $context
     * @param ImplementationOfThirdInterface $exA
     */
    public function __construct(\ContextFirst $context, \ImplementationOfThirdInterface $exA)
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
     * @var FourthInterface
     */
    protected $_exB;

    /**
     * @param ContextFirst $context
     * @param FourthInterface $exB
     */
    public function __construct(\ContextFirst $context, \FourthInterface $exB)
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
     * @var ThirdInterface
     */
    protected $_exA;

    /**
     * @param ContextFirst $context
     * @param ThirdInterface $exA
     */
    public function __construct(\ContextFirst $context, \ThirdInterface $exA)
    {
        $this->_context = $context;
        $this->_exA = $exA;
    }
}
