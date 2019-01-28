<?php
/**
 * \Magento\Framework\DB\Profiler test case
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Test\Unit;

class ProfilerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Profiler instance for test
     * @var \Magento\Framework\DB\Profiler
     */
    protected $_profiler;

    /**
     * Setup
     */
    protected function setUp()
    {
        $this->_profiler = new \Magento\Framework\DB\Profiler(true);
    }

    public function testSetHost()
    {
        $this->_profiler->setHost('localhost');
        $this->assertAttributeSame('localhost', '_host', $this->_profiler);
    }

    public function testSetType()
    {
        $this->_profiler->setType('mysql');
        $this->assertAttributeSame('mysql', '_type', $this->_profiler);
    }

    public function testQueryStart()
    {
        $lastQueryId = $this->_profiler->queryStart('SELECT * FROM table');
        $this->assertSame(null, $lastQueryId);
    }

    public function testQueryEnd()
    {
        $lastQueryId = $this->_profiler->queryStart('SELECT * FROM table');
        $endResult = $this->_profiler->queryEnd($lastQueryId);
        $this->assertAttributeSame(null, '_lastQueryId', $this->_profiler);
        $this->assertSame(\Magento\Framework\DB\Profiler::STORED, $endResult);
    }

    public function testQueryEndLast()
    {
        $this->_profiler->queryStart('SELECT * FROM table');
        $endResult = $this->_profiler->queryEndLast();
        $this->assertAttributeSame(null, '_lastQueryId', $this->_profiler);
        $this->assertSame(\Magento\Framework\DB\Profiler::STORED, $endResult);

        $endResult = $this->_profiler->queryEndLast();
        $this->assertSame(\Magento\Framework\DB\Profiler::IGNORED, $endResult);
    }
}
