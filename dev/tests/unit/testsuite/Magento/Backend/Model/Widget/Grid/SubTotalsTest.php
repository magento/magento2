<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Widget\Grid;

class SubTotalsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var $_model \Magento\Backend\Model\Widget\Grid\SubTotals
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
        $this->_parserMock = $this->getMock(
            'Magento\Backend\Model\Widget\Grid\Parser',
            [],
            [],
            '',
            false,
            false,
            false
        );

        $this->_factoryMock = $this->getMock(
            'Magento\Framework\Object\Factory',
            ['create'],
            [],
            '',
            false,
            false,
            false
        );
        $this->_factoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->with(
            ['sub_test1' => 3, 'sub_test2' => 2]
        )->will(
            $this->returnValue(new \Magento\Framework\Object(['sub_test1' => 3, 'sub_test2' => 2]))
        );

        $arguments = ['factory' => $this->_factoryMock, 'parser' => $this->_parserMock];

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_model = $objectManagerHelper->getObject('Magento\Backend\Model\Widget\Grid\SubTotals', $arguments);

        // setup columns
        $columns = ['sub_test1' => 'sum', 'sub_test2' => 'avg'];
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
        $expected = new \Magento\Framework\Object(['sub_test1' => 3, 'sub_test2' => 2]);
        $this->assertEquals($expected, $this->_model->countTotals($this->_getTestCollection()));
    }

    /**
     * Retrieve test collection
     *
     * @return \Magento\Framework\Data\Collection
     */
    protected function _getTestCollection()
    {
        $collection = new \Magento\Framework\Data\Collection(
            $this->getMock('Magento\Core\Model\EntityFactory', [], [], '', false)
        );
        $items = [
            new \Magento\Framework\Object(['sub_test1' => '1', 'sub_test2' => '2']),
            new \Magento\Framework\Object(['sub_test1' => '1', 'sub_test2' => '2']),
            new \Magento\Framework\Object(['sub_test1' => '1', 'sub_test2' => '2']),
        ];
        foreach ($items as $item) {
            $collection->addItem($item);
        }

        return $collection;
    }
}
