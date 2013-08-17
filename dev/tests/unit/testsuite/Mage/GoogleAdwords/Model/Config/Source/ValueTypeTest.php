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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_GoogleAdwords_Model_Config_Source_ValueTypeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_helperMock;

    /**
     * @var Mage_GoogleAdwords_Model_Config_Source_ValueType
     */
    protected $_model;

    public function setUp()
    {
        $this->_helperMock = $this->getMock('Mage_GoogleAdwords_Helper_Data', array('__'), array(), '', false);
        $this->_helperMock->expects($this->atLeastOnce())->method('__')->with($this->isType('string'))
            ->will($this->returnArgument(0));

        $objectManager = new Magento_Test_Helper_ObjectManager($this);
        $this->_model = $objectManager->getObject('Mage_GoogleAdwords_Model_Config_Source_ValueType', array(
            'helper' => $this->_helperMock,
        ));
    }

    public function testToOptionArray()
    {
        $this->assertEquals(array(
            array(
                'value' => Mage_GoogleAdwords_Helper_Data::CONVERSION_VALUE_TYPE_DYNAMIC,
                'label' => 'Dynamic',
            ),
            array(
                'value' => Mage_GoogleAdwords_Helper_Data::CONVERSION_VALUE_TYPE_CONSTANT,
                'label' => 'Constant',
            ),
        ), $this->_model->toOptionArray());
    }
}
