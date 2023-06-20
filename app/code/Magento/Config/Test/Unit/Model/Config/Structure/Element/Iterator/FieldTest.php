<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Model\Config\Structure\Element\Iterator;

use Magento\Config\Model\Config\Structure\Element\Group;
use Magento\Config\Model\Config\Structure\Element\Iterator\Field;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FieldTest extends TestCase
{
    /**
     * @var Field
     */
    protected $_model;

    /**
     * @var MockObject
     */
    protected $_fieldMock;

    /**
     * @var MockObject
     */
    protected $_groupMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->_fieldMock = $this->createMock(\Magento\Config\Model\Config\Structure\Element\Field::class);
        $this->_groupMock = $this->createMock(Group::class);
        $this->_model = new Field(
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

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        unset($this->_fieldMock);
        unset($this->_groupMock);
        unset($this->_model);
    }

    /**
     * @return void
     */
    public function testIteratorInitializesCorrespondingFlyweights(): void
    {
        $this->_groupMock
            ->method('setData')
            ->withConsecutive(
                [
                    ['_elementType' => 'group', 'id' => 'someGroup_1'],
                    'scope'
                ],
                [
                    ['_elementType' => 'group', 'id' => 'someGroup_2'],
                    'scope'
                ]
            );
        $this->_groupMock->expects($this->any())->method('isVisible')->willReturn(true);

        $this->_fieldMock
            ->method('setData')
            ->withConsecutive(
                [
                    ['_elementType' => 'field', 'id' => 'someField_1'],
                    'scope'
                ],
                [
                    ['_elementType' => 'field', 'id' => 'someField_2'],
                    'scope'
                ]
            );
        $this->_fieldMock->expects($this->any())->method('isVisible')->willReturn(true);
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
