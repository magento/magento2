<?php
/**
 * \Magento\Framework\DB\Profiler test case
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\DB;

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
