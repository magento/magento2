<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Indexer\Test\Unit;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\Table\Strategy;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StrategyTest extends TestCase
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
     * @var ResourceConnection|MockObject
     */
    protected $_resourceMock;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->_resourceMock = $this->createMock(ResourceConnection::class);
        $this->_model = new Strategy(
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
        $this->assertFalse($this->_model->getUseIdxTable());
        $this->_model->setUseIdxTable(false);
        $this->assertFalse($this->_model->getUseIdxTable());
        $this->_model->setUseIdxTable(true);
        $this->assertTrue($this->_model->getUseIdxTable());
        $this->_model->setUseIdxTable();
        $this->assertFalse($this->_model->getUseIdxTable());
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
        $this->_resourceMock->expects($this->any())->method('getTableName')->willReturnCallback(
            function ($tableName) use ($prefix) {
                return $prefix . $tableName;
            }
        );
        $this->assertEquals('pre_test_tmp', $this->_model->getTableName('test'));
        $this->_model->setUseIdxTable(true);
        $this->assertEquals('pre_test_idx', $this->_model->getTableName('test'));
    }
}
