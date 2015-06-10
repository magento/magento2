<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Model\Indexer\Table;

/**
 * Class StrategyTest
 * @package Magento\Indexer\Test\Unit\Model\Indexer\Table
 */
class StrategyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Strategy object
     *
     * @var \Magento\Indexer\Model\Indexer\Table\Strategy
     */
    protected $_model;

    /**
     * Resource mock
     *
     * @var \Magento\Framework\App\Resource|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resourceMock;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->_resourceMock = $this->getMock(
            '\Magento\Framework\App\Resource',
            [],
            [],
            '',
            false
        );
        $this->_model = new \Magento\Indexer\Model\Indexer\Table\Strategy(
            $this->_resourceMock
        );
    }

    /**
     * Test use idx table switcher
     */
    public function testUseIdxTable()
    {
        $this->assertEquals(false, $this->_model->useIdxTable());
        $this->assertEquals(false, $this->_model->useIdxTable(false));
        $this->assertEquals(true, $this->_model->useIdxTable(true));
        $this->assertEquals(true, $this->_model->useIdxTable());
        $this->assertEquals(false, $this->_model->useIdxTable(false));
        $this->assertEquals(false, $this->_model->useIdxTable());
    }

    /**
     * Test table name preparation
     */
    public function testPrepareTableName()
    {
        $this->assertEquals('test_tmp', $this->_model->prepareTableName('test'));
        $this->_model->useIdxTable(true);
        $this->assertEquals('test_idx', $this->_model->prepareTableName('test'));
        $this->_model->useIdxTable(false);
        $this->assertEquals('test_tmp', $this->_model->prepareTableName('test'));
    }

    /**
     * Test table name getter
     */
    public function testGetTableName()
    {
        $prefix = 'pre_';
        $this->_resourceMock->expects($this->any())->method('getTableName')->will($this->returnCallback(
                function ($tableName) use ($prefix) {
                    return $prefix . $tableName;
                }
            )
        );
        $this->assertEquals('pre_test_tmp', $this->_model->getTableName('test'));
        $this->_model->useIdxTable(true);
        $this->assertEquals('pre_test_idx', $this->_model->getTableName('test'));
    }
}
