<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Model\Config\Structure\Element;

use Magento\Config\Model\Config\Structure\Element\Field;
use Magento\Config\Model\Config\Structure\Element\Group;
use Magento\Config\Model\Config\Structure\Element\Iterator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IteratorTest extends TestCase
{
    /**
     * @var Iterator
     */
    protected $_model;

    /**
     * @var MockObject
     */
    protected $_flyweightMock;

    protected function setUp(): void
    {
        $elementData = ['group1' => ['id' => 1], 'group2' => ['id' => 2], 'group3' => ['id' => 3]];
        $this->_flyweightMock = $this->createMock(Group::class);

        $this->_model = new Iterator($this->_flyweightMock);
        $this->_model->setElements($elementData, 'scope');
    }

    protected function tearDown(): void
    {
        unset($this->_model);
        unset($this->_flyweightMock);
    }

    public function testIteratorInitializesFlyweight()
    {
        $this->_flyweightMock->expects($this->at(0))->method('setData')->with(['id' => 1], 'scope');
        $this->_flyweightMock->expects($this->at(2))->method('setData')->with(['id' => 2], 'scope');
        $this->_flyweightMock->expects($this->at(4))->method('setData')->with(['id' => 3], 'scope');
        $this->_flyweightMock->expects($this->any())->method('isVisible')->willReturn(true);
        $counter = 0;
        foreach ($this->_model as $item) {
            $this->assertEquals($this->_flyweightMock, $item);
            $counter++;
        }
        $this->assertEquals(3, $counter);
    }

    public function testIteratorSkipsNonValidElements()
    {
        $this->_flyweightMock->expects($this->exactly(3))->method('isVisible')->willReturn(false);
        $this->_flyweightMock->expects($this->exactly(3))->method('setData');
        foreach ($this->_model as $item) {
            unset($item);
            $this->fail('Iterator shows non visible fields');
        }
    }

    /**
     * @param string $elementId
     * @param bool $result
     * @dataProvider isLastDataProvider
     */
    public function testIsLast($elementId, $result)
    {
        $elementMock = $this->createMock(Field::class);
        $elementMock->expects($this->once())->method('getId')->willReturn($elementId);
        $this->assertEquals($result, $this->_model->isLast($elementMock));
    }

    /**
     * @return array
     */
    public function isLastDataProvider()
    {
        return [[1, false], [2, false], [3, true]];
    }
}
