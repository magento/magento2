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
class Mage_Webhook_Formatter_FactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Config
     */
    public $mockConfig;

    /**
     * @var Mage_Webhook_Model_Formatter_Factory
     */
    public $mockFormatterFactory;

    public function setUp()
    {
        parent::setUp();
        $this->mockConfig         = $this->getMock('Mage_Core_Model_Config', array('getNode'), array(), '', false);
        $this->mockFormatterFactory =
                $this->getMockBuilder('Mage_Webhook_Model_Formatter_Factory')->setMethods(array('getModel'))
                        ->setConstructorArgs(array($this->mockConfig))->getMock();
    }

    /**
     * Tests the case where the config has and entry for the format and it is of the correct type.
     */
    public function testGetFormatterFactory()
    {
        $this->mockConfig->expects($this->once())->method('getNode')->with(
            Mage_Webhook_Model_Formatter_Factory::XML_PATH_FORMATS . 'json/formatter_factory'
        )->will($this->returnValue('Mage_Webhook_Model_Formatter_Factory_Default'));
        $this->mockFormatterFactory->expects($this->once())->method('getModel')
                ->with('Mage_Webhook_Model_Formatter_Factory_Default')
                ->will($this->returnValue(new Mage_Webhook_Model_Formatter_Factory_Json($this->mockConfig)));
        $formatterFactory = $this->mockFormatterFactory
            ->getFormatterFactory(Mage_Webhook_Model_Subscriber::FORMAT_JSON);
        $this->assertInstanceOf('Mage_Webhook_Model_Formatter_Factory_Json', $formatterFactory);
    }

    /**
     * Tests the case where the config xml does not have an entry for the format name.
     * @expectedException LogicException
     * @expectedExceptionMessage Wrong Format name WrongFormatName.
     */
    public function testGetFormatterFactoryWrongFormatName()
    {
        $this->mockFormatterFactory->getFormatterFactory('WrongFormatName');
    }

    /**
     * Tests the case where the config xml actually has an entry for the format,
     * but it is not a valid type i.e. Mage_Webhook_Model_Formatter_Factory_Interface.
     * @expectedException LogicException
     * @expectedExceptionMessage Wrong Formatter type for format
     */
    public function testGetFormatterFactoryWrongFormatterClass()
    {
        $formatName = 'wrongformatname';
        $this->mockConfig->expects($this->once())->method('getNode')->with(
            Mage_Webhook_Model_Formatter_Factory::XML_PATH_FORMATS . $formatName .'/formatter_factory'
        )->will($this->returnValue('Mage'));
        $this->mockFormatterFactory->expects($this->once())->method('getModel')->with('Mage')
                ->will($this->returnValue(new Mage()));

        $this->mockFormatterFactory->getFormatterFactory($formatName);
    }
}
