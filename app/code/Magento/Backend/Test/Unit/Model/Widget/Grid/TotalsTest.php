<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Model\Widget\Grid;

use Magento\Backend\Model\Widget\Grid\Parser;
use Magento\Backend\Model\Widget\Grid\Totals;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TotalsTest extends TestCase
{
    /**
     * @var Totals $_model
     */
    protected $_model;

    /**
     * @var MockObject
     */
    protected $_parserMock;

    /**
     * @var MockObject
     */
    protected $_factoryMock;

    protected function setUp(): void
    {
        // prepare model
        $this->_parserMock = $this->createPartialMock(
            Parser::class,
            ['parseExpression']
        );

        $this->_factoryMock = $this->createPartialMock(Factory::class, ['create']);

        $createValueMap = [
            [['test1' => 3, 'test2' => 2], new DataObject(['test1' => 3, 'test2' => 2])],
            [['test4' => 9, 'test5' => 2], new DataObject(['test4' => 9, 'test5' => 2])],
        ];
        $this->_factoryMock->expects($this->any())->method('create')->willReturnMap($createValueMap);

        $arguments = ['factory' => $this->_factoryMock, 'parser' => $this->_parserMock];

        $objectManagerHelper = new ObjectManager($this);
        $this->_model = $objectManagerHelper->getObject(Totals::class, $arguments);

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
        $collection = new Collection(
            $this->createMock(EntityFactory::class)
        );
        $items = [
            new DataObject(['test1' => '1', 'test2' => '2']),
            new DataObject(['test1' => '1', 'test2' => '2']),
            new DataObject(['test1' => '1', 'test2' => '2']),
        ];
        foreach ($items as $item) {
            $collection->addItem($item);
        }

        $expected = new DataObject(['test1' => 3, 'test2' => 2]);
        $this->assertEquals($expected, $this->_model->countTotals($collection));
    }

    public function testCountTotalsWithSubItems()
    {
        $this->_model->reset(true);
        $this->_model->setColumn('test4', 'sum');
        $this->_model->setColumn('test5', 'avg');

        // prepare collection
        $collection = new Collection(
            $this->createMock(EntityFactory::class)
        );
        $items = [
            new DataObject(
                [
                    'children' => new DataObject(['test4' => '1', 'test5' => '2']),
                ]
            ),
            new DataObject(
                [
                    'children' => new DataObject(['test4' => '1', 'test5' => '2']),
                ]
            ),
            new DataObject(
                [
                    'children' => new DataObject(['test4' => '1', 'test5' => '2']),
                ]
            ),
        ];
        foreach ($items as $item) {
            // prepare sub-collection
            $subCollection = new Collection(
                $this->createMock(EntityFactory::class)
            );
            $subCollection->addItem(new DataObject(['test4' => '1', 'test5' => '2']));
            $subCollection->addItem(new DataObject(['test4' => '2', 'test5' => '2']));
            $item->setChildren($subCollection);
            $collection->addItem($item);
        }
        $expected = new DataObject(['test4' => 9, 'test5' => 2]);
        $this->assertEquals($expected, $this->_model->countTotals($collection));
    }
}
