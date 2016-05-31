<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer\Test\Unit;

/**
 * Class StrategyTest
 * @package Magento\Indexer\Test\Unit\Model\Indexer\Table
 */
class StrategyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Strategy object
     *
     * @var \Magento\Framework\Indexer\Table\Strategy
     */
    protected $_model;

    /**
     * Resource mock
     *
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resourceMock;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->_resourceMock = $this->getMock(
            '\Magento\Framework\App\ResourceConnection',
            [],
            [],
            '',
            false
        );
        $this->_model = new \Magento\Framework\Indexer\Table\Strategy(
            $this->_resourceMock
        );
    }

    /**
     * Test use idx table switcher
     *
     * @return void
     */
    public function testUseIdxTable()
    {
        $this->assertEquals(false, $this->_model->getUseIdxTable());
        $this->_model->setUseIdxTable(false);
        $this->assertEquals(false, $this->_model->getUseIdxTable());
        $this->_model->setUseIdxTable(true);
        $this->assertEquals(true, $this->_model->getUseIdxTable());
        $this->_model->setUseIdxTable();
        $this->assertEquals(false, $this->_model->getUseIdxTable());
    }

    /**
     * Test table name preparation
     *
     * @return void
     */
    public function testPrepareTableName()
    {
        $this->assertEquals('test_tmp', $this->_model->prepareTableName('test'));
        $this->_model->setUseIdxTable(true);
        $this->assertEquals('test_idx', $this->_model->prepareTableName('test'));
        $this->_model->setUseIdxTable(false);
        $this->assertEquals('test_tmp', $this->_model->prepareTableName('test'));
    }

    /**
     * Test table name getter
     *
     * @return void
     */
    public function testGetTableName()
    {
        $prefix = 'pre_';
        $this->_resourceMock->expects($this->any())->method('getTableName')->will(
            $this->returnCallback(
                function ($tableName) use ($prefix) {
                    return $prefix . $tableName;
                }
            )
        );
        $this->assertEquals('pre_test_tmp', $this->_model->getTableName('test'));
        $this->_model->setUseIdxTable(true);
        $this->assertEquals('pre_test_idx', $this->_model->getTableName('test'));
    }
}
