<?php
/**
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
interface InterfaceA
{
}
class ImplementationOfInterfaceA implements InterfaceA
{
}
interface InterfaceB
{
}
class ImplementationOfInterfaceB implements InterfaceB
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
     * @var InterfaceA
     */
    protected $_interfaceA;

    /**
     * @var ImplementationOfInterfaceB
     */
    protected $_implOfBInterface;

    public function __construct(
        \ClassA $exA,
        \ClassB $exB,
        \ClassC $exC,
        \InterfaceA $interfaceA,
        \ImplementationOfInterfaceB $implOfBInterface
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
    public function __construct(\Context $context, array $data = array())
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
    public function __construct(\Context $context, \ClassB $exB, \ClassC $exC, array $data = array())
    {
        parent::__construct($context, $exB);
        $this->_context = $context;
        $this->_exB = $exB;
        $this->_exC = $exC;
        $this->_data = $data;
    }
}
