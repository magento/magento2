<?php
/**
 * \Magento\Core\Model\DataService\Invoker
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\DataService;

class InvokerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Fake info for service and classes.
     */
    const TEST_CLASS_NAME = 'TEST_CLASS_NAME';

    const TEST_DATA_SERVICE_NAME = 'TEST_DATA_SERVICE_NAME';

    const TEST_NAMESPACE = 'TEST_NAMESPACE';

    const TEST_NAMESPACE_ALIAS = 'TEST_NAMESPACE_ALIAS';

    /**
     * @var \Magento\Core\Model\DataService\Invoker
     */
    protected $_invoker;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configMock;

    /**
     * @var  \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var  \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_compositeMock;

    /**
     * Empty data service array
     *
     * @var array
     */
    protected $_dataServiceMock;

    /**
     * @var  \PHPUnit_Framework_MockObject_MockObject
     */
    private $_navigator;

    /**
     * Get the data service mock
     *
     * @return array
     */
    public function retrieveMethod()
    {
        return $this->_dataServiceMock;
    }

    protected function setUp()
    {
        $this->_configMock = $this->getMockBuilder('Magento\Core\Model\DataService\ConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_objectManagerMock = $this->getMockBuilder('Magento\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_compositeMock = $this->getMockBuilder('Magento\Core\Model\DataService\Path\Composite')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_navigator = $this->getMockBuilder('Magento\Core\Model\DataService\Path\Navigator')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_invoker = new \Magento\Core\Model\DataService\Invoker(
            $this->_configMock,
            $this->_objectManagerMock,
            $this->_compositeMock,
            $this->_navigator
        );
        $this->_dataServiceMock = array();
    }

    public function testGetServiceData()
    {
        $classInformation = array(
            'class'          => self::TEST_CLASS_NAME,
            'retrieveMethod' => 'retrieveMethod', 'methodArguments' => array());
        $this->_configMock
            ->expects($this->once())
            ->method("getClassByAlias")
            ->with($this->equalTo(self::TEST_DATA_SERVICE_NAME))
            ->will($this->returnValue($classInformation));
        $this->_objectManagerMock
            ->expects($this->once())
            ->method("get")
            ->with($this->equalTo(self::TEST_CLASS_NAME))
            ->will($this->returnValue($this));

        $this->assertSame(
            $this->_dataServiceMock,
            $this->_invoker->getServiceData(self::TEST_DATA_SERVICE_NAME)
        );
    }

    public function testGetServiceDataWithArguments()
    {
        $classInformation = array(
            'class'          => self::TEST_CLASS_NAME,
            'retrieveMethod' => 'retrieveMethod', 'methodArguments' => array('something'));
        $this->_configMock
            ->expects($this->once())
            ->method("getClassByAlias")
            ->with($this->equalTo(self::TEST_DATA_SERVICE_NAME))
            ->will($this->returnValue($classInformation));
        $this->_objectManagerMock
            ->expects($this->once())
            ->method("get")
            ->with($this->equalTo(self::TEST_CLASS_NAME))
            ->will($this->returnValue($this));

        $this->assertSame(
            $this->_dataServiceMock,
            $this->_invoker->getServiceData(self::TEST_DATA_SERVICE_NAME)
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage return an array
     */
    public function testGetServiceDataFailsIfNotArray()
    {
        // This line makes sure we don't return an array
        $this->_dataServiceMock = (object)array();
        $classInformation = array(
            'class'          => self::TEST_CLASS_NAME,
            'retrieveMethod' => 'retrieveMethod', 'methodArguments' => array());
        $this->_configMock
            ->expects($this->once())
            ->method("getClassByAlias")
            ->with($this->equalTo(self::TEST_DATA_SERVICE_NAME))
            ->will($this->returnValue($classInformation));
        $this->_objectManagerMock
            ->expects($this->once())
            ->method("get")
            ->with($this->equalTo(self::TEST_CLASS_NAME))
            ->will($this->returnValue($this));

        $this->_invoker->getServiceData(self::TEST_DATA_SERVICE_NAME);
    }

    public function testGetArgumentValueNoReplace()
    {
        $expectedValue = 'simple_value';
        $this->_navigator->expects($this->never())
            ->method('search');

        $argumentValue = $this->_invoker->getArgumentValue($expectedValue);

        $this->assertEquals($expectedValue, $argumentValue);
    }

    public function testGetArgumentValueFullReplace()
    {
        $expectedValue = 'replacementValue';
        $this->_navigator->expects($this->once())
            ->method('search')
            ->with($this->_compositeMock, array('first', 'second'))
            ->will($this->returnValue($expectedValue));

        $argumentValue = $this->_invoker->getArgumentValue('{{first.second}}');

        $this->assertEquals($expectedValue, $argumentValue);
    }

    public function testGetArgumentValueTwoReplace()
    {
        $replaceFirstSecond = 'replacementValue';
        $replaceAnother = 'anotherValue';
        $inputValue = 'prefix-{{first.second}}-middle-{{another}}-postfix';
        $expectedValue = 'prefix-replacementValue-middle-anotherValue-postfix';
        $this->_navigator->expects($this->at(0))
            ->method('search')
            ->with($this->_compositeMock, array('first', 'second'))
            ->will($this->returnValue($replaceFirstSecond));
        $this->_navigator->expects($this->at(1))
            ->method('search')
            ->with($this->_compositeMock, array('another'))
            ->will($this->returnValue($replaceAnother));

        $argumentValue = $this->_invoker->getArgumentValue($inputValue);

        $this->assertEquals($expectedValue, $argumentValue);
    }
}
