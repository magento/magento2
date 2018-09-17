<?php
/**
 * \Magento\Framework\DB\Profiler test case
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Test\Unit;

class ProfilerTest extends \PHPUnit_Framework_TestCase
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
        $this->assertAttributeEquals('localhost', '_host', $this->_profiler);
    }

    public function testSetType()
    {
        $this->_profiler->setType('mysql');
        $this->assertAttributeEquals('mysql', '_type', $this->_profiler);
    }

    public function testQueryStart()
    {
        $lastQueryId = $this->_profiler->queryStart('SELECT * FROM table');
        $this->assertEquals(null, $lastQueryId);
    }

    public function testQueryEnd()
    {
        $lastQueryId = $this->_profiler->queryStart('SELECT * FROM table');
        $endResult = $this->_profiler->queryEnd($lastQueryId);
        $this->assertAttributeEquals(null, '_lastQueryId', $this->_profiler);
        $this->assertEquals(\Magento\Framework\DB\Profiler::STORED, $endResult);
    }

    public function testQueryEndLast()
    {
        $this->_profiler->queryStart('SELECT * FROM table');
        $endResult = $this->_profiler->queryEndLast();
        $this->assertAttributeEquals(null, '_lastQueryId', $this->_profiler);
        $this->assertEquals(\Magento\Framework\DB\Profiler::STORED, $endResult);

        $endResult = $this->_profiler->queryEndLast();
        $this->assertEquals(\Magento\Framework\DB\Profiler::IGNORED, $endResult);
    }
}
