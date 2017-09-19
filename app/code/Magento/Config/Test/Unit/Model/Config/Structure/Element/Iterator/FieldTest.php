<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Model\Config\Structure\Element\Iterator;

class FieldTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Config\Model\Config\Structure\Element\Iterator\Field
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_fieldMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_groupMock;

    protected function setUp()
    {
        $this->_fieldMock = $this->createMock(\Magento\Config\Model\Config\Structure\Element\Field::class);
        $this->_groupMock = $this->createMock(\Magento\Config\Model\Config\Structure\Element\Group::class);
        $this->_model = new \Magento\Config\Model\Config\Structure\Element\Iterator\Field(
            $this->_groupMock,
            $this->_fieldMock
        );
        $this->_model->setElements(
            [
                'someGroup_1' => ['_elementType' => 'group', 'id' => 'someGroup_1'],
                'someField_1' => ['_elementType' => 'field', 'id' => 'someField_1'],
                'someGroup_2' => ['_elementType' => 'group', 'id' => 'someGroup_2'],
                'someField_2' => ['_elementType' => 'field', 'id' => 'someField_2'],
            ],
            'scope'
        );
    }

    protected function tearDown()
    {
        unset($this->_fieldMock);
        unset($this->_groupMock);
        unset($this->_model);
    }

    public function testIteratorInitializesCorrespondingFlyweights()
    {
        $this->_groupMock->expects(
            $this->at(0)
        )->method(
            'setData'
        )->with(
            ['_elementType' => 'group', 'id' => 'someGroup_1'],
            'scope'
        );
        $this->_groupMock->expects(
            $this->at(2)
        )->method(
            'setData'
        )->with(
            ['_elementType' => 'group', 'id' => 'someGroup_2'],
            'scope'
        );
        $this->_groupMock->expects($this->any())->method('isVisible')->will($this->returnValue(true));

        $this->_fieldMock->expects(
            $this->at(0)
        )->method(
            'setData'
        )->with(
            ['_elementType' => 'field', 'id' => 'someField_1'],
            'scope'
        );
        $this->_fieldMock->expects(
            $this->at(2)
        )->method(
            'setData'
        )->with(
            ['_elementType' => 'field', 'id' => 'someField_2'],
            'scope'
        );
        $this->_fieldMock->expects($this->any())->method('isVisible')->will($this->returnValue(true));

        $items = [];
        foreach ($this->_model as $item) {
            $items[] = $item;
        }
        $this->assertEquals($this->_groupMock, $items[0]);
        $this->assertEquals($this->_fieldMock, $items[1]);
        $this->assertEquals($this->_groupMock, $items[2]);
        $this->assertEquals($this->_fieldMock, $items[3]);
    }
}
