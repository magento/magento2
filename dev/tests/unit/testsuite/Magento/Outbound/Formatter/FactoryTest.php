<?php
/**
 * \Magento\Outbound\Formatter\Factory
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
namespace Magento\Outbound\Formatter;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject \Magento\App\ObjectManager
     */
    private $_mockObjectManager;

    /**
     * @var \Magento\Outbound\Formatter\Factory
     */
    protected $_formatterFactory;

    /**
     * @var \Magento\Outbound\Formatter\Json
     */
    protected $_expectedObject;

    protected function setUp()
    {
        $this->_mockObjectManager = $this->getMockBuilder('Magento\ObjectManager')
            ->setMethods(array('get'))
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->_expectedObject = $this->getMockBuilder('Magento\Outbound\Formatter\Json')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_formatterFactory = new \Magento\Outbound\Formatter\Factory(
            array('json' => 'Test_Formatter_Json'),
            $this->_mockObjectManager
        );
    }

    public function testGetFormatter()
    {
        $this->_mockObjectManager->expects($this->once())
            ->method('get')
            ->with('Test_Formatter_Json')
            ->will($this->returnValue($this->_expectedObject));

        $formatter = $this->_formatterFactory->getFormatter(\Magento\Outbound\EndpointInterface::FORMAT_JSON);
        $this->assertInstanceOf('Magento\Outbound\Formatter\Json', $formatter);
        $this->assertEquals($this->_expectedObject, $formatter);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage WrongFormatName
     */
    public function testGetFormatterWrongFormatName()
    {
        $this->_formatterFactory->getFormatter('WrongFormatName');
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Formatter class for json does not implement FormatterInterface.
     */
    public function testGetFormatterWrongFormatterClass()
    {
        $this->_mockObjectManager->expects($this->once())
            ->method('get')
            ->with('Test_Formatter_Json')
            ->will($this->returnValue($this->getMock('Magento\Object')));

        $this->_formatterFactory->getFormatter(\Magento\Outbound\EndpointInterface::FORMAT_JSON);
    }
}
