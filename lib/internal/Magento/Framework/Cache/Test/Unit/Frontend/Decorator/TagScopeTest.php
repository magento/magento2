<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Test\Unit\Frontend\Decorator;

use Magento\Framework\Cache\Frontend\Decorator\TagScope;
use Magento\Framework\Cache\FrontendInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TagScopeTest extends TestCase
{
    /**
     * @var TagScope
     */
    protected $_object;

    /**
     * @var MockObject
     */
    protected $_frontend;

    protected function setUp(): void
    {
        $this->_frontend = $this->getMockForAbstractClass(FrontendInterface::class);
        $this->_object = new TagScope($this->_frontend, 'enforced_tag');
    }

    protected function tearDown(): void
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
        )->willReturn(
            $expectedResult
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
        )->willReturn(
            $expectedResult
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
        )->willReturn(
            $expectedResult
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
        )->willReturn(
            $fixtureResultOne
        );
        $this->_frontend->expects(
            $this->at(1)
        )->method(
            'clean'
        )->with(
            \Zend_Cache::CLEANING_MODE_MATCHING_TAG,
            ['test_tag_two', 'enforced_tag']
        )->willReturn(
            $fixtureResultTwo
        );
        $actualResult = $this->_object->clean(
            \Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG,
            ['test_tag_one', 'test_tag_two']
        );
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @return array
     */
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
