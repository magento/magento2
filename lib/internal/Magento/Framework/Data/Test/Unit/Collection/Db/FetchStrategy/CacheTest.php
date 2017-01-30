<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Test\Unit\Collection\Db\FetchStrategy;

class CacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Data\Collection\Db\FetchStrategy\Cache
     */
    private $_object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_cache;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_fetchStrategy;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_select;

    /**
     * @var array
     */
    private $_fixtureData = [
        ['column_one' => 'row_one_value_one', 'column_two' => 'row_one_value_two'],
        ['column_one' => 'row_two_value_one', 'column_two' => 'row_two_value_two'],
    ];

    protected function setUp()
    {
        $this->_select = $this->getMock('Magento\Framework\DB\Select', ['assemble'], [], '', false);
        $this->_select->expects(
            $this->once()
        )->method(
            'assemble'
        )->will(
            $this->returnValue('SELECT * FROM fixture_table')
        );

        $this->_cache = $this->getMockForAbstractClass('Magento\Framework\Cache\FrontendInterface');
        $this->_fetchStrategy = $this->getMockForAbstractClass(
            'Magento\Framework\Data\Collection\Db\FetchStrategyInterface'
        );

        $this->_object = new \Magento\Framework\Data\Collection\Db\FetchStrategy\Cache(
            $this->_cache,
            $this->_fetchStrategy,
            'fixture_',
            ['fixture_tag_one', 'fixture_tag_two'],
            86400
        );
    }

    protected function tearDown()
    {
        $this->_object = null;
        $this->_cache = null;
        $this->_fetchStrategy = null;
        $this->_select = null;
    }

    public function testFetchAllCached()
    {
        $this->_cache->expects(
            $this->once()
        )->method(
            'load'
        )->with(
            'fixture_06a6b0cfd83bf997e76b1b403df86569'
        )->will(
            $this->returnValue(serialize($this->_fixtureData))
        );
        $this->_fetchStrategy->expects($this->never())->method('fetchAll');
        $this->_cache->expects($this->never())->method('save');
        $this->assertEquals($this->_fixtureData, $this->_object->fetchAll($this->_select, []));
    }

    public function testFetchAllDelegation()
    {
        $cacheId = 'fixture_06a6b0cfd83bf997e76b1b403df86569';
        $bindParams = ['param_one' => 'value_one', 'param_two' => 'value_two'];
        $this->_cache->expects($this->once())->method('load')->with($cacheId)->will($this->returnValue(false));
        $this->_fetchStrategy->expects(
            $this->once()
        )->method(
            'fetchAll'
        )->with(
            $this->_select,
            $bindParams
        )->will(
            $this->returnValue($this->_fixtureData)
        );
        $this->_cache->expects(
            $this->once()
        )->method(
            'save'
        )->with(
            serialize($this->_fixtureData),
            $cacheId,
            ['fixture_tag_one', 'fixture_tag_two'],
            86400
        );
        $this->assertEquals($this->_fixtureData, $this->_object->fetchAll($this->_select, $bindParams));
    }
}
