<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Cache\Test\Unit\Frontend\Decorator;

class TagScopeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Cache\Frontend\Decorator\TagScope
     */
    protected $_object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_frontend;

    protected function setUp()
    {
        $this->_frontend = $this->getMock(\Magento\Framework\Cache\FrontendInterface::class);
        $this->_object = new \Magento\Framework\Cache\Frontend\Decorator\TagScope($this->_frontend, 'enforced_tag');
    }

    protected function tearDown()
    {
        $this->_object = null;
        $this->_frontend = null;
    }

    public function testGetTag()
    {
        $this->assertEquals('enforced_tag', $this->_object->getTag());
    }

    public function testSave()
    {
        $expectedResult = new \stdClass();
        $this->_frontend->expects(
            $this->once()
        )->method(
            'save'
        )->with(
            'test_value',
            'test_id',
            ['test_tag_one', 'test_tag_two', 'enforced_tag'],
            111
        )->will(
            $this->returnValue($expectedResult)
        );
        $actualResult = $this->_object->save('test_value', 'test_id', ['test_tag_one', 'test_tag_two'], 111);
        $this->assertSame($expectedResult, $actualResult);
    }

    public function testCleanModeAll()
    {
        $expectedResult = new \stdClass();
        $this->_frontend->expects(
            $this->once()
        )->method(
            'clean'
        )->with(
            \Zend_Cache::CLEANING_MODE_MATCHING_TAG,
            ['enforced_tag']
        )->will(
            $this->returnValue($expectedResult)
        );
        $actualResult = $this->_object->clean(
            \Zend_Cache::CLEANING_MODE_ALL,
            ['ignored_tag_one', 'ignored_tag_two']
        );
        $this->assertSame($expectedResult, $actualResult);
    }

    public function testCleanModeMatchingTag()
    {
        $expectedResult = new \stdClass();
        $this->_frontend->expects(
            $this->once()
        )->method(
            'clean'
        )->with(
            \Zend_Cache::CLEANING_MODE_MATCHING_TAG,
            ['test_tag_one', 'test_tag_two', 'enforced_tag']
        )->will(
            $this->returnValue($expectedResult)
        );
        $actualResult = $this->_object->clean(
            \Zend_Cache::CLEANING_MODE_MATCHING_TAG,
            ['test_tag_one', 'test_tag_two']
        );
        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * @param bool $fixtureResultOne
     * @param bool $fixtureResultTwo
     * @param bool $expectedResult
     * @dataProvider cleanModeMatchingAnyTagDataProvider
     */
    public function testCleanModeMatchingAnyTag($fixtureResultOne, $fixtureResultTwo, $expectedResult)
    {
        $this->_frontend->expects(
            $this->at(0)
        )->method(
            'clean'
        )->with(
            \Zend_Cache::CLEANING_MODE_MATCHING_TAG,
            ['test_tag_one', 'enforced_tag']
        )->will(
            $this->returnValue($fixtureResultOne)
        );
        $this->_frontend->expects(
            $this->at(1)
        )->method(
            'clean'
        )->with(
            \Zend_Cache::CLEANING_MODE_MATCHING_TAG,
            ['test_tag_two', 'enforced_tag']
        )->will(
            $this->returnValue($fixtureResultTwo)
        );
        $actualResult = $this->_object->clean(
            \Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG,
            ['test_tag_one', 'test_tag_two']
        );
        $this->assertEquals($expectedResult, $actualResult);
    }

    public function cleanModeMatchingAnyTagDataProvider()
    {
        return [
            'failure, failure' => [false, false, false],
            'failure, success' => [false, true, true],
            'success, failure' => [true, false, true],
            'success, success' => [true, true, true]
        ];
    }
}
