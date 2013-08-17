  <?php
/**
 * Mage_Webhook_Model_Formatter_Factory
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
 * @category    Mage
 * @package     Mage_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Magento_Outbound_Formatter_FactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject Mage_Core_Model_ObjectManager
     */
    private $_mockObjectManager;

    /**
     * @var Magento_Outbound_Formatter_Factory
     */
    protected $_formatterFactory;

    /**
     * @var Magento_Outbound_Formatter_Json
     */
    protected $_expectedObject;

    protected function setUp()
    {
        $this->_mockObjectManager = $this->getMockBuilder('Magento_ObjectManager')
            ->setMethods(array('get'))
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->_expectedObject = $this->getMockBuilder('Magento_Outbound_Formatter_Json')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_formatterFactory = new Magento_Outbound_Formatter_Factory(
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

        $formatter = $this->_formatterFactory->getFormatter(Magento_Outbound_EndpointInterface::FORMAT_JSON);
        $this->assertInstanceOf('Magento_Outbound_Formatter_Json', $formatter);
        $this->assertEquals($this->_expectedObject, $formatter);
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage WrongFormatName
     */
    public function testGetFormatterWrongFormatName()
    {
        $this->_formatterFactory->getFormatter('WrongFormatName');
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage Formatter class for json does not implement FormatterInterface.
     */
    public function testGetFormatterWrongFormatterClass()
    {
        $this->_mockObjectManager->expects($this->once())
            ->method('get')
            ->with('Test_Formatter_Json')
            ->will($this->returnValue($this->getMock('Varien_Object')));

        $this->_formatterFactory->getFormatter(Magento_Outbound_EndpointInterface::FORMAT_JSON);
    }
}
