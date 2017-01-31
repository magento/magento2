<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\Cache\Type;

use Magento\Framework\App\Cache\Type\FrontendPool;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Cache\Type\Config
     */
    protected $model;

    /**
     * @var \Magento\Framework\Cache\FrontendInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $frontendMock;

    protected function setUp()
    {
        $cacheFrontendPoolMock = $this->getMockBuilder('Magento\Framework\App\Cache\Type\FrontendPool')
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = (new ObjectManager($this))->getObject(
            'Magento\Framework\App\Cache\Type\Config',
            ['cacheFrontendPool' => $cacheFrontendPoolMock]
        );
        $this->frontendMock = $this->getMock('Magento\Framework\Cache\FrontendInterface');
        $cacheFrontendPoolMock->expects($this->once())
            ->method('get')
            ->with(\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER)
            ->willReturn($this->frontendMock);
    }

    /**
     * @param string $method
     * @param array $params
     * @param mixed $expectedResult
     * @dataProvider proxyMethodDataProvider
     */
    public function testProxyMethod($method, $params, $expectedResult)
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ProxyTesting();
        $result = $helper->invokeWithExpectations($this->model, $this->frontendMock, $method, $params, $expectedResult);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function proxyMethodDataProvider()
    {
        return [
            ['test', ['record_id'], 111],
            ['load', ['record_id'], '111'],
            ['remove', ['record_id'], true],
            ['getBackend', [], $this->getMock('Zend_Cache_Backend')],
            ['getLowLevelFrontend', [], $this->getMock('Zend_Cache_Core')],
        ];
    }

    public function testSave()
    {
        $expectedResult = new \stdClass();
        $this->frontendMock->expects(
            $this->once()
        )->method(
            'save'
        )->with(
            'test_value',
            'test_id',
            ['test_tag_one', 'test_tag_two', \Magento\Framework\App\Cache\Type\Config::CACHE_TAG],
            111
        )->will(
            $this->returnValue($expectedResult)
        );
        $actualResult = $this->model->save('test_value', 'test_id', ['test_tag_one', 'test_tag_two'], 111);
        $this->assertSame($expectedResult, $actualResult);
    }

    public function testCleanModeAll()
    {
        $expectedResult = new \stdClass();
        $this->frontendMock->expects(
            $this->once()
        )->method(
            'clean'
        )->with(
            \Zend_Cache::CLEANING_MODE_MATCHING_TAG,
            [\Magento\Framework\App\Cache\Type\Config::CACHE_TAG]
        )->will(
            $this->returnValue($expectedResult)
        );
        $actualResult = $this->model->clean(
            \Zend_Cache::CLEANING_MODE_ALL,
            ['ignored_tag_one', 'ignored_tag_two']
        );
        $this->assertSame($expectedResult, $actualResult);
    }

    public function testCleanModeMatchingTag()
    {
        $expectedResult = new \stdClass();
        $this->frontendMock->expects(
            $this->once()
        )->method(
            'clean'
        )->with(
            \Zend_Cache::CLEANING_MODE_MATCHING_TAG,
            ['test_tag_one', 'test_tag_two', \Magento\Framework\App\Cache\Type\Config::CACHE_TAG]
        )->will(
            $this->returnValue($expectedResult)
        );
        $actualResult = $this->model->clean(
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
        $this->frontendMock->expects(
            $this->at(0)
        )->method(
            'clean'
        )->with(
            \Zend_Cache::CLEANING_MODE_MATCHING_TAG,
            ['test_tag_one', \Magento\Framework\App\Cache\Type\Config::CACHE_TAG]
        )->will(
            $this->returnValue($fixtureResultOne)
        );
        $this->frontendMock->expects(
            $this->at(1)
        )->method(
            'clean'
        )->with(
            \Zend_Cache::CLEANING_MODE_MATCHING_TAG,
            ['test_tag_two', \Magento\Framework\App\Cache\Type\Config::CACHE_TAG]
        )->will(
            $this->returnValue($fixtureResultTwo)
        );
        $actualResult = $this->model->clean(
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
