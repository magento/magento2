<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Model\Widget\Grid;

class TotalsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var $_model \Magento\Backend\Model\Widget\Grid\Totals
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_parserMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_factoryMock;

    protected function setUp()
    {
        // prepare model
        $this->_parserMock = $this->getMock(
            'Magento\Backend\Model\Widget\Grid\Parser',
            ['parseExpression'],
            [],
            '',
            false,
            false,
            false
        );

        $this->_factoryMock = $this->getMock(
            'Magento\Framework\DataObject\Factory',
            ['create'],
            [],
            '',
            false,
            false,
            false
        );

        $createValueMap = [
            [['test1' => 3, 'test2' => 2], new \Magento\Framework\DataObject(['test1' => 3, 'test2' => 2])],
            [['test4' => 9, 'test5' => 2], new \Magento\Framework\DataObject(['test4' => 9, 'test5' => 2])],
        ];
        $this->_factoryMock->expects($this->any())->method('create')->will($this->returnValueMap($createValueMap));

        $arguments = ['factory' => $this->_factoryMock, 'parser' => $this->_parserMock];

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_model = $objectManagerHelper->getObject('Magento\Backend\Model\Widget\Grid\Totals', $arguments);

        // setup columns
        $columns = ['test1' => 'sum', 'test2' => 'avg'];
        foreach ($columns as $index => $expression) {
            $this->_model->setColumn($index, $expression);
        }
    }

    protected function tearDown()
    {
        unset($this->_parserMock);
        unset($this->_factoryMock);
    }

    public function testCountTotals()
    {
        // prepare collection
        $collection = new \Magento\Framework\Data\Collection(
            $this->getMock('Magento\Framework\Data\Collection\EntityFactory', [], [], '', false)
        );
        $items = [
            new \Magento\Framework\DataObject(['test1' => '1', 'test2' => '2']),
            new \Magento\Framework\DataObject(['test1' => '1', 'test2' => '2']),
            new \Magento\Framework\DataObject(['test1' => '1', 'test2' => '2']),
        ];
        foreach ($items as $item) {
            $collection->addItem($item);
        }

        $expected = new \Magento\Framework\DataObject(['test1' => 3, 'test2' => 2]);
        $this->assertEquals($expected, $this->_model->countTotals($collection));
    }

    public function testCountTotalsWithSubItems()
    {
        $this->_model->reset(true);
        $this->_model->setColumn('test4', 'sum');
        $this->_model->setColumn('test5', 'avg');

        // prepare collection
        $collection = new \Magento\Framework\Data\Collection(
            $this->getMock('Magento\Framework\Data\Collection\EntityFactory', [], [], '', false)
        );
        $items = [
            new \Magento\Framework\DataObject(
                [
                    'children' => new \Magento\Framework\DataObject(['test4' => '1', 'test5' => '2']),
                ]
            ),
            new \Magento\Framework\DataObject(
                [
                    'children' => new \Magento\Framework\DataObject(['test4' => '1', 'test5' => '2']),
                ]
            ),
            new \Magento\Framework\DataObject(
                [
                    'children' => new \Magento\Framework\DataObject(['test4' => '1', 'test5' => '2']),
                ]
            ),
        ];
        foreach ($items as $item) {
            // prepare sub-collection
            $subCollection = new \Magento\Framework\Data\Collection(
                $this->getMock('Magento\Framework\Data\Collection\EntityFactory', [], [], '', false)
            );
            $subCollection->addItem(new \Magento\Framework\DataObject(['test4' => '1', 'test5' => '2']));
            $subCollection->addItem(new \Magento\Framework\DataObject(['test4' => '2', 'test5' => '2']));
            $item->setChildren($subCollection);
            $collection->addItem($item);
        }
        $expected = new \Magento\Framework\DataObject(['test4' => 9, 'test5' => 2]);
        $this->assertEquals($expected, $this->_model->countTotals($collection));
    }
}
