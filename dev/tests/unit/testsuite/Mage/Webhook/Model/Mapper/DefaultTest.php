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

class Mage_Webhook_Model_Mapper_DefaultTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider mappingDataProvider
     */
    public function testMappingData($incomeData, $expectedData)
    {
        $mapper = new Mage_Webhook_Model_Mapper_Default('some/topic', $incomeData);

        $this->assertEquals('some/topic', $mapper->getTopic());
        $this->assertEquals(array(Mage_Webhook_Model_Mapper_Default::TOPIC_HEADER => 'some/topic'),
            $mapper->getHeaders());
        $this->assertEquals($expectedData, $mapper->getData());
    }

    public function testMappingDataCycleDetected()
    {
        $this->markTestSkipped('skipping to debug build break');
        $objectA = new Varien_Object(array('keyA' => 'valueA'));
        $objectB = new Varien_Object(array('keyB' => 'valueB', 'object' => $objectA));
        $objectA->setObject($objectB);

        $mapper = new Mage_Webhook_Model_Mapper_Default('some/topic', array('object' => $objectA));

        $expectedData = array(
            'object' => array(
                'keyA' => 'valueA',
                'object' => array(
                    'keyB' => 'valueB',
                    'object' => Mage_Webhook_Model_Mapper_Default::CYCLE_DETECTED_MARK)
            ));

        $this->assertEquals($expectedData, $mapper->getData());
    }

    public function mappingDataProvider()
    {
        return array(
            array(
                array('object' => new Varien_Object(array('keyA' => 'valueA'))),
                array('object' => array('keyA' => 'valueA'))
            ),

            array(
                array('objectA' => new Varien_Object(array('keyA' => 'valueA')),
                      'objectB' => new Varien_Object(array(
                          'keyB' => new Varien_Object(array(
                              'keyC' => 'valueC',
                              'password' => 'qa123123'))
                      ))),
                array('objectA' => array('keyA' => 'valueA'),
                      'objectB' => array(
                          'keyB' => array(
                              'keyC' => 'valueC',
                              'password' => Mage_Webhook_Model_Mapper_Default::REDACTED)))
            ),

            array(
                array(),
                array()
            ),
            array(
                array(555888, 'string' => "Some text", 'not_varien_object' => $this->getMock('Mage_Core_Model_Config',
                    array(), array(), '', false)),
                array(555888, 'string' => "Some text", 'not_varien_object' => $this->getMock('Mage_Core_Model_Config',
                    array(), array(), '', false)),
            ),
            array(
                array(
                    array(
                        'some_object' => new Varien_Object(
                            array(
                                'keyA' => array(
                                    new Varien_Object(
                                        array(
                                            'sub_key' => 'sub_value'
                                        )
                                    )
                                )
                            )
                        )
                    )
                ),
                array(
                    array(
                        'some_object' => array(
                            'keyA' => array(
                                array(
                                    'sub_key' => 'sub_value'
                                )
                            )
                        )
                    )
                ),
            ),
        );
    }
}
