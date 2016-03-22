<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Model\Widget\Grid;

class AbstractTotalsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var $_model \PHPUnit_Framework_MockObject_MockObject
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

    /**
     * Columns map for parserMock return expressions
     *
     * @var array
     */
    protected $_columnsValueMap;

    protected function setUp()
    {
        $this->_prepareParserMock();
        $this->_prepareFactoryMock();

        $arguments = ['factory' => $this->_factoryMock, 'parser' => $this->_parserMock];
        $this->_model = $this->getMockForAbstractClass(
            'Magento\Backend\Model\Widget\Grid\AbstractTotals',
            $arguments,
            '',
            true,
            false,
            true,
            []
        );
        $this->_model->expects($this->any())->method('_countSum')->will($this->returnValue(2));
        $this->_model->expects($this->any())->method('_countAverage')->will($this->returnValue(2));

        $this->_setUpColumns();
    }

    protected function tearDown()
    {
        unset($this->_parserMock);
        unset($this->_factoryMock);
    }

    /**
     * Retrieve test collection
     *
     * @return \Magento\Framework\Data\Collection
     */
    protected function _getTestCollection()
    {
        $collection = new \Magento\Framework\Data\Collection(
            $this->getMock('Magento\Framework\Data\Collection\EntityFactory', [], [], '', false)
        );
        $items = [new \Magento\Framework\DataObject(['test1' => '1', 'test2' => '2'])];
        foreach ($items as $item) {
            $collection->addItem($item);
        }

        return $collection;
    }

    /**
     * Prepare tested model by setting columns
     */
    protected function _setUpColumns()
    {
        $columns = [
            'test1' => 'sum',
            'test2' => 'avg',
            'test3' => 'test1+test2',
            'test4' => 'test1-test2',
            'test5' => 'test1*test2',
            'test6' => 'test1/test2',
            'test7' => 'test1/0',
        ];

        foreach ($columns as $index => $expression) {
            $this->_model->setColumn($index, $expression);
        }
    }

    /**
     * Prepare parser mock by setting test expressions for columns and operation used
     */
    protected function _prepareParserMock()
    {
        $this->_parserMock = $this->getMock(
            'Magento\Backend\Model\Widget\Grid\Parser',
            ['parseExpression', 'isOperation']
        );

        $columnsValueMap = [
            ['test1+test2', ['test1', 'test2', '+']],
            ['test1-test2', ['test1', 'test2', '-']],
            ['test1*test2', ['test1', 'test2', '*']],
            ['test1/test2', ['test1', 'test2', '/']],
            ['test1/0', ['test1', '0', '/']],
        ];
        $this->_parserMock->expects(
            $this->any()
        )->method(
            'parseExpression'
        )->will(
            $this->returnValueMap($columnsValueMap)
        );

        $isOperationValueMap = [
            ['+', true],
            ['-', true],
            ['*', true],
            ['/', true],
            ['test1', false],
            ['test2', false],
            ['0', false],
        ];
        $this->_parserMock->expects(
            $this->any()
        )->method(
            'isOperation'
        )->will(
            $this->returnValueMap($isOperationValueMap)
        );
    }

    /**
     * Prepare factory mock for setting possible values
     */
    protected function _prepareFactoryMock()
    {
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
            [
                [
                    'test1' => 2,
                    'test2' => 2,
                    'test3' => 4,
                    'test4' => 0,
                    'test5' => 4,
                    'test6' => 1,
                    'test7' => 0,
                ],
                new \Magento\Framework\DataObject(
                    [
                        'test1' => 2,
                        'test2' => 2,
                        'test3' => 4,
                        'test4' => 0,
                        'test5' => 4,
                        'test6' => 1,
                        'test7' => 0,
                    ]
                ),
            ],
            [[], new \Magento\Framework\DataObject()],
        ];
        $this->_factoryMock->expects($this->any())->method('create')->will($this->returnValueMap($createValueMap));
    }

    public function testColumns()
    {
        $expected = [
            'test1' => 'sum',
            'test2' => 'avg',
            'test3' => 'test1+test2',
            'test4' => 'test1-test2',
            'test5' => 'test1*test2',
            'test6' => 'test1/test2',
            'test7' => 'test1/0',
        ];

        $this->assertEquals($expected, $this->_model->getColumns());
    }

    public function testCountTotals()
    {
        $expected = new \Magento\Framework\DataObject(
            ['test1' => 2, 'test2' => 2, 'test3' => 4, 'test4' => 0, 'test5' => 4, 'test6' => 1, 'test7' => 0]
        );
        $this->assertEquals($expected, $this->_model->countTotals($this->_getTestCollection()));
    }

    public function testReset()
    {
        $this->_model->countTotals($this->_getTestCollection());
        $this->_model->reset();

        $this->assertEquals(new \Magento\Framework\DataObject(), $this->_model->getTotals());
        $this->assertNotEmpty($this->_model->getColumns());
    }

    public function testResetFull()
    {
        $this->_model->countTotals($this->_getTestCollection());
        $this->_model->reset(true);

        $this->assertEquals(new \Magento\Framework\DataObject(), $this->_model->getTotals());
        $this->assertEmpty($this->_model->getColumns());
    }
}
