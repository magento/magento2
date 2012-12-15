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
 * @category    Magento
 * @package     Mage_Backend
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Backend_Model_Config_Structure_Element_Iterator_FieldTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Backend_Model_Config_Structure_Element_Iterator_Field
     */
    protected $_model;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_fieldMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_groupMock;

    public function setUp()
    {
        $this->_fieldMock = $this->getMock(
            'Mage_Backend_Model_Config_Structure_Element_Field', array(), array(), '', false
        );
        $this->_groupMock = $this->getMock(
            'Mage_Backend_Model_Config_Structure_Element_Group', array(), array(), '', false
        );
        $this->_model = new Mage_Backend_Model_Config_Structure_Element_Iterator_Field(
            $this->_groupMock, $this->_fieldMock
        );
        $this->_model->setElements(array(
            'someGroup_1' => array(
                '_elementType' => 'group',
                'id' => 'someGroup_1'
            ),
            'someField_1' => array(
                '_elementType' => 'field',
                'id' => 'someField_1'
            ),
            'someGroup_2' => array(
                '_elementType' => 'group',
                'id' => 'someGroup_2'
            ),
            'someField_2' => array(
                '_elementType' => 'field',
                'id' => 'someField_2'
            )
        ), 'scope');
    }

    protected function tearDown()
    {
        unset($this->_fieldMock);
        unset($this->_groupMock);
        unset($this->_model);
    }

    public function testIteratorInitializesCorrespondingFlyweights()
    {
        $this->_groupMock->expects($this->at(0))->method('setData')
            ->with(array('_elementType' => 'group', 'id' => 'someGroup_1'), 'scope');
        $this->_groupMock->expects($this->at(2))->method('setData')
            ->with(array('_elementType' => 'group', 'id' => 'someGroup_2'), 'scope');
        $this->_groupMock->expects($this->any())->method('isVisible')->will($this->returnValue(true));

        $this->_fieldMock->expects($this->at(0))->method('setData')
            ->with(array('_elementType' => 'field', 'id' => 'someField_1'), 'scope');
        $this->_fieldMock->expects($this->at(2))->method('setData')
            ->with(array('_elementType' => 'field', 'id' => 'someField_2'), 'scope');
        $this->_fieldMock->expects($this->any())->method('isVisible')->will($this->returnValue(true));

        $items = array();
        foreach ($this->_model as $item) {
            $items[] = $item;
        }
        $this->assertEquals($this->_groupMock, $items[0]);
        $this->assertEquals($this->_fieldMock, $items[1]);
        $this->assertEquals($this->_groupMock, $items[2]);
        $this->assertEquals($this->_fieldMock, $items[3]);
    }
}

