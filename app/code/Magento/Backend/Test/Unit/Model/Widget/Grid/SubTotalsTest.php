<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Model\Widget\Grid;

use Magento\Backend\Model\Widget\Grid\Parser;
use Magento\Backend\Model\Widget\Grid\SubTotals;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SubTotalsTest extends TestCase
{
    /**
     * @var SubTotals $_model
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
        $this->_parserMock = $this->createMock(Parser::class);

        $this->_factoryMock = $this->createPartialMock(Factory::class, ['create']);
        $this->_factoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->with(
            ['sub_test1' => 3, 'sub_test2' => 2]
        )->willReturn(
            new DataObject(['sub_test1' => 3, 'sub_test2' => 2])
        );

        $arguments = ['factory' => $this->_factoryMock, 'parser' => $this->_parserMock];

        $objectManagerHelper = new ObjectManager($this);
        $this->_model = $objectManagerHelper->getObject(
            SubTotals::class,
            $arguments
        );

        // setup columns
        $columns = ['sub_test1' => 'sum', 'sub_test2' => 'avg'];
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
        $expected = new DataObject(['sub_test1' => 3, 'sub_test2' => 2]);
        $this->assertEquals($expected, $this->_model->countTotals($this->_getTestCollection()));
    }

    /**
     * Retrieve test collection
     *
     * @return Collection
     */
    protected function _getTestCollection()
    {
        $collection = new Collection(
            $this->createMock(EntityFactory::class)
        );
        $items = [
            new DataObject(['sub_test1' => '1', 'sub_test2' => '2']),
            new DataObject(['sub_test1' => '1', 'sub_test2' => '2']),
            new DataObject(['sub_test1' => '1', 'sub_test2' => '2']),
        ];
        foreach ($items as $item) {
            $collection->addItem($item);
        }

        return $collection;
    }
}
