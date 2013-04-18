<?php
/**
 * The list of all expected soap fault XMLs.
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

require_once __DIR__ . '/_files/mapper_stubs.php';

class Mage_Webhook_Model_Mapper_FactoryTest extends PHPUnit_Framework_TestCase
{

    /** @var PHPUnit_Framework_MockObject_MockObject */
    protected $_objectManagerMock;

    /** @var Mage_Webhook_Model_Mapper_Factory */
    protected $_mapperFactory;

    protected function setUp()
    {
        $this->_objectManagerMock = $this->getMockBuilder('Magento_ObjectManager')
            ->disableOriginalConstructor()
            ->setMethods(array('create'))
            ->getMockForAbstractClass();

        $this->_mapperFactory = new Mage_Webhook_Model_Mapper_Factory($this->_objectManagerMock);
        parent::setUp();
    }

    public function testGetMapperFactory()
    {
        $config = new Mage_Core_Model_Config_Element('<mappings>
            <testMapping>
                <mapper_factory>Stub_Mapper_Factory_Default</mapper_factory>
            </testMapping>
        </mappings>');

        $this->_objectManagerMock->expects($this->any())
            ->method('create')->with($this->equalTo('Stub_Mapper_Factory_Default'))
            ->will($this->returnValue(new Stub_Mapper_Factory_Default()));


        $defaultMapper = $this->_mapperFactory->getMapperFactory('testMapping', $config);

        $this->assertInstanceOf('Stub_Mapper_Factory_Default', $defaultMapper);
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage Wrong Mapper type for mapping WrongMapperClass.
     */
    public function testGetMapperFactoryWrongMapperClass()
    {
        $config = new Mage_Core_Model_Config_Element('<mappings>
            <WrongMapperClass>
                <mapper_factory>Stub_Mapper_Wrong_Mapper_Factory_Model</mapper_factory>
            </WrongMapperClass>
        </mappings>');

        $this->_objectManagerMock->expects($this->any())
            ->method('create')->with($this->equalTo('Stub_Mapper_Wrong_Mapper_Factory_Model'))
            ->will($this->returnValue(new Stub_Mapper_Wrong_Mapper_Factory_Model()));

        $this->_mapperFactory->getMapperFactory('WrongMapperClass', $config);
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage Wrong mapping name WrongMappingName.
     */
    public function testGetMapperFactoryWrongMappingName()
    {
        $config = new Mage_Core_Model_Config_Element('<mappings>
            <testMapping>
                <mapper_factory>Stub_Mapper_Factory_Default</mapper_factory>
            </testMapping>
        </mappings>');

        $this->_objectManagerMock->expects($this->any())
            ->method('create')->with($this->equalTo('Stub_Mapper_Factory_Default'))
            ->will($this->returnValue(new Stub_Mapper_Factory_Default()));

        $this->_mapperFactory->getMapperFactory('WrongMappingName', $config);
    }
}
