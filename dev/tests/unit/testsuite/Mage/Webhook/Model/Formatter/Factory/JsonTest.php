<?php
/**
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
class Mage_Webhook_Model_Formatter_Factory_JsonTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Config
     */
    public $mockConfig;

    /**
     * @var Mage_Webhook_Model_Formatter_Factory_Json
     */
    public $mockFormatterFactory;

    public function setUp()
    {
        parent::setUp();
        $this->mockConfig         = $this->getMock('Mage_Core_Model_Config', array('getNode'), array(), '', false);
        $this->mockFormatterFactory =
                $this->getMockBuilder('Mage_Webhook_Model_Formatter_Factory_Json')->setMethods(array('getModel'))
                        ->setConstructorArgs(array($this->mockConfig))->getMock();
    }

    public function testGetFormatter()
    {
        $mapping = 'default';
        $this->mockConfig->expects($this->once())->method('getNode')->with(
            Mage_Webhook_Model_Formatter_Factory_Json::XML_PATH_DEFAULT_OPTIONS . "format/$mapping/formatter"
        )->will($this->returnValue('Mage_Webhook_Model_Formatter_Json'));
        $this->mockFormatterFactory->expects($this->once())->method('getModel')
            ->with('Mage_Webhook_Model_Formatter_Json')
            ->will($this->returnValue(new Mage_Webhook_Model_Formatter_Json()));
        $formatter = $this->mockFormatterFactory->getFormatter($mapping);
        $this->assertInstanceOf('Mage_Webhook_Model_Formatter_Interface', $formatter);
    }

    public function testGetDefaultFormatter()
    {
        $mapping = 'unknown_mapping';
        $this->mockConfig->expects($this->at(0))->method('getNode')->with(
            Mage_Webhook_Model_Formatter_Factory_Json::XML_PATH_DEFAULT_OPTIONS . "format/$mapping/formatter"
        )->will($this->returnValue(null));
        $this->mockConfig->expects($this->at(1))->method('getNode')->with(
            Mage_Webhook_Model_Formatter_Factory_Json::XML_PATH_DEFAULT_OPTIONS . 'default_formatter'
        )->will($this->returnValue('Mage_Webhook_Model_Formatter_Json'));
        $this->mockFormatterFactory->expects($this->once())->method('getModel')
            ->with('Mage_Webhook_Model_Formatter_Json')
            ->will($this->returnValue(new Mage_Webhook_Model_Formatter_Json()));
        $formatter = $this->mockFormatterFactory->getFormatter($mapping);
        $this->assertInstanceOf('Mage_Webhook_Model_Formatter_Interface', $formatter);
    }

    /**
     * Tests the case where the config xml does not have an entry for the format name.
     * @expectedException LogicException
     * @expectedExceptionMessage There is no specific formatter for the format given unknown_mapping and no default
     * formatter.
     */
    public function testNoFormatterNorDefaultFormatter()
    {
        $mapping = 'unknown_mapping';
        $this->mockConfig->expects($this->at(0))->method('getNode')->with(
            Mage_Webhook_Model_Formatter_Factory_Json::XML_PATH_DEFAULT_OPTIONS . "format/$mapping/formatter"
        )->will($this->returnValue(null));
        $this->mockConfig->expects($this->at(1))->method('getNode')->with(
            Mage_Webhook_Model_Formatter_Factory_Json::XML_PATH_DEFAULT_OPTIONS . 'default_formatter'
        )->will($this->returnValue(null));
        $this->mockFormatterFactory->getFormatter($mapping);
    }

    /**
     * Tests the case where the config xml does not specify an object of the correct formatter class
     * @expectedException LogicException
     * @expectedExceptionMessage Wrong Formatter type for the model found given the format default.
     */
    public function testGetFormatterWrongFormatterClass()
    {
        $mapping = 'default';
        $this->mockConfig->expects($this->once())->method('getNode')->with(
            Mage_Webhook_Model_Formatter_Factory_Json::XML_PATH_DEFAULT_OPTIONS . "format/$mapping/formatter"
        )->will($this->returnValue('Mage'));
        $this->mockFormatterFactory->expects($this->once())->method('getModel')->with('Mage')
                ->will($this->returnValue(new Mage()));
        $this->mockFormatterFactory->getFormatter($mapping);
    }
}
