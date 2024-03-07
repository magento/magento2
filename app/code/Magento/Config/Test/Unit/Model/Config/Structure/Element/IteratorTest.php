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

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $elementData = ['group1' => ['id' => 1], 'group2' => ['id' => 2], 'group3' => ['id' => 3]];
        $this->_flyweightMock = $this->createMock(Group::class);

        $this->_model = new Iterator($this->_flyweightMock);
        $this->_model->setElements($elementData, 'scope');
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        unset($this->_model);
        unset($this->_flyweightMock);
    }

    /**
     * @return void
     */
    public function testIteratorInitializesFlyweight(): void
    {
        $this->_flyweightMock
            ->method('setData')
            ->willReturnCallback(function ($arg1, $arg2) {
                if ($arg1['id'] == 1 && $arg2 == 'scope') {
                    return null;
                } elseif ($arg1['id'] == 2 && $arg2 == 'scope') {
                    return null;
                } elseif ($arg1['id'] == 3 && $arg2 == 'scope') {
                    return null;
                }
            });

        $this->_flyweightMock->expects($this->any())->method('isVisible')->willReturn(true);
        $counter = 0;
        foreach ($this->_model as $item) {
            $this->assertEquals($this->_flyweightMock, $item);
            $counter++;
        }
        $this->assertEquals(3, $counter);
    }

    public function testIteratorSkipsNonValidElements(): void
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
    public function testIsLast($elementId, $result): void
    {
        $elementMock = $this->createMock(Field::class);
        $elementMock->expects($this->once())->method('getId')->willReturn($elementId);
        $this->assertEquals($result, $this->_model->isLast($elementMock));
    }

    /**
     * @return array
     */
    public static function isLastDataProvider(): array
    {
        return [[1, false], [2, false], [3, true]];
    }
}
