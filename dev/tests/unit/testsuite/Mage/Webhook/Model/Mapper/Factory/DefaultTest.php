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

require_once __DIR__ . '/../_files/mapper_stubs.php';

class Mage_Webhook_Model_Mapper_Factory_DefaultTest extends PHPUnit_Framework_TestCase
{

    /** @var Magento_ObjectManager */
    protected $_objectManagerMock;

    protected function setUp()
    {
        $this->_objectManagerMock = $this->getMockBuilder('Magento_ObjectManager')
            ->disableOriginalConstructor()
            ->setMethods(array('create'))
            ->getMockForAbstractClass();

        parent::setUp();
    }

    /**
     * @dataProvider getMapperProvider
     */
    public function testGetMapper($configXml, $expectedMapperClass)
    {
        $config = new Mage_Core_Model_Config_Element($configXml);

        $this->_objectManagerMock->expects($this->any())
            ->method('create')->will($this->returnCallback(array($this, 'callbackCreateClass')));
        $defaultFactory = new Mage_Webhook_Model_Mapper_Factory_Default($this->_objectManagerMock);
        $mapper = $defaultFactory->getMapper('one/two', array(), $config);

        $this->assertInstanceOf($expectedMapperClass, $mapper);
    }

    public function callbackCreateClass($name)
    {
        $mockFactory = null;
        $mockFactoryBuilder = $this->getMockBuilder($name)
            ->disableOriginalConstructor()
            ->setMethods(null);
        if ($name == 'Mage_Webhook_Model_Mapper_Default_Factory') {
            $mockFactoryBuilder->setMethods(array('getMapper'));
            $mockFactory = $mockFactoryBuilder->getMock();
            $mockDefault = $this->getMockBuilder('Mage_Webhook_Model_Mapper_Default')
                ->disableOriginalConstructor()
                ->getMock();
            $mockFactory->expects($this->any())
                ->method('getMapper')
                ->will($this->returnValue($mockDefault));
        } else {
            $mockFactory = $mockFactoryBuilder->getMock();
        }
        return $mockFactory;
    }

    public function getMapperProvider()
    {
        return array(
            array('<options>
                    <default_mapper>Stub_Mapper_Default_Mapper_Factory</default_mapper>
                    <topics>
                        <one>
                            <two>
                                <options><model>Stub_Mapper_Topic_OneTwo_Mapper_Factory</model></options>
                            </two>
                        </one>
                    </topics>
                 </options>',
                'Stub_Mapper_Topic_OneTwo_Mapper'
            ),
            array('<options>
                    <topics>
                        <one>
                            <two>
                                <options><model>Stub_Mapper_Topic_OneTwo_Mapper_Factory</model></options>
                            </two>
                        </one>
                    </topics>
                 </options>',
                'Stub_Mapper_Topic_OneTwo_Mapper'
            ),
            array('<options>
                    <default_mapper>Stub_Mapper_Default_Mapper_Factory</default_mapper>
                    <topics>
                        <one>
                            <two>
                            </two>
                        </one>
                    </topics>
                 </options>',
                'Stub_Mapper_Default_Mapper'
            ),
            array('<options>
                    <default_mapper>Stub_Mapper_Default_Mapper_Factory</default_mapper>
                    <topics>
                        <one>
                            <two>
                                <options><model>Stub_Mapper_Wrong_Mapper_Factory_Model</model></options>
                            </two>
                        </one>
                    </topics>
                 </options>',
                'Stub_Mapper_Default_Mapper'
            ),
            array('<options>
                    <topics>
                        <one>
                            <two>
                                <options><model></model></options>
                            </two>
                        </one>
                    </topics>
                 </options>',
                'Mage_Webhook_Model_Mapper_Default'
            ),
            array('<options>
                    <default_mapper>Stub_Mapper_Wrong_Mapper_Factory_Model</default_mapper>
                    <topics>
                        <one>
                            <two>
                                <options><model></model></options>
                            </two>
                        </one>
                    </topics>
                 </options>',
                'Mage_Webhook_Model_Mapper_Default'
            ),
        );
    }
}
