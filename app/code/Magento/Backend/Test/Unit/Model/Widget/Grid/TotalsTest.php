<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Model\Widget\Grid;

class TotalsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var $_model \Magento\Backend\Model\Widget\Grid\Totals
     */
    protected $_model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_parserMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_factoryMock;

    protected function setUp(): void
    {
        // prepare model
        $this->_parserMock = $this->createPartialMock(
            \Magento\Backend\Model\Widget\Grid\Parser::class,
            ['parseExpression']
        );

        $this->_factoryMock = $this->createPartialMock(\Magento\Framework\DataObject\Factory::class, ['create']);

        $createValueMap = [
            [['test1' => 3, 'test2' => 2], new \Magento\Framework\DataObject(['test1' => 3, 'test2' => 2])],
            [['test4' => 9, 'test5' => 2], new \Magento\Framework\DataObject(['test4' => 9, 'test5' => 2])],
        ];
        $this->_factoryMock->expects($this->any())->method('create')->willReturnMap($createValueMap);

        $arguments = ['factory' => $this->_factoryMock, 'parser' => $this->_parserMock];

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_model = $objectManagerHelper->getObject(\Magento\Backend\Model\Widget\Grid\Totals::class, $arguments);

        // setup columns
        $columns = ['test1' => 'sum', 'test2' => 'avg'];
        foreach ($columns as $index => $expression) {
            $this->_model->setColumn($index, $expression);
        }
    }

    protected function tearDown(): void
    {
        unset($this->_parserMock);
        unset($this->_factoryMock);
    }

    public function testCountTotals()
    {
        // prepare collection
        $collection = new \Magento\Framework\Data\Collection(
            $this->createMock(\Magento\Framework\Data\Collection\EntityFactory::class)
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
            $this->createMock(\Magento\Framework\Data\Collection\EntityFactory::class)
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
                $this->createMock(\Magento\Framework\Data\Collection\EntityFactory::class)
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
