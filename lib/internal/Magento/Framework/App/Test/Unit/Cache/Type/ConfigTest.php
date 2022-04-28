<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Cache\Type;

use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\App\Cache\Type\FrontendPool;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\ProxyTesting;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @var Config
     */
    protected $model;

    /**
     * @var FrontendInterface|MockObject
     */
    protected $frontendMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $cacheFrontendPoolMock = $this->getMockBuilder(FrontendPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = (new ObjectManager($this))->getObject(
            Config::class,
            ['cacheFrontendPool' => $cacheFrontendPoolMock]
        );
        $this->frontendMock = $this->getMockForAbstractClass(FrontendInterface::class);
        $cacheFrontendPoolMock->expects($this->once())
            ->method('get')
            ->with(Config::TYPE_IDENTIFIER)
            ->willReturn($this->frontendMock);
    }

    /**
     * @param string $method
     * @param array $params
     * @param mixed $expectedResult
     *
     * @return void
     * @dataProvider proxyMethodDataProvider
     */
    public function testProxyMethod($method, $params, $expectedResult): void
    {
        $helper = new ProxyTesting();
        $result = $helper->invokeWithExpectations($this->model, $this->frontendMock, $method, $params, $expectedResult);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function proxyMethodDataProvider(): array
    {
        return [
            ['test', ['record_id'], 111],
            ['load', ['record_id'], '111'],
            ['remove', ['record_id'], true],
            ['getBackend', [], $this->createMock(\Zend_Cache_Backend::class)],
            ['getLowLevelFrontend', [], $this->createMock(\Zend_Cache_Core::class)]
        ];
    }

    public function testSave(): void
    {
        $expectedResult = new \stdClass();
        $this->frontendMock->expects(
            $this->once()
        )->method(
            'save'
        )->with(
            'test_value',
            'test_id',
            ['test_tag_one', 'test_tag_two', Config::CACHE_TAG],
            111
        )->willReturn(
            $expectedResult
        );
        $actualResult = $this->model->save('test_value', 'test_id', ['test_tag_one', 'test_tag_two'], 111);
        $this->assertSame($expectedResult, $actualResult);
    }

    public function testCleanModeAll(): void
    {
        $expectedResult = new \stdClass();
        $this->frontendMock->expects(
            $this->once()
        )->method(
            'clean'
        )->with(
            \Zend_Cache::CLEANING_MODE_MATCHING_TAG,
            [Config::CACHE_TAG]
        )->willReturn(
            $expectedResult
        );
        $actualResult = $this->model->clean(
            \Zend_Cache::CLEANING_MODE_ALL,
            ['ignored_tag_one', 'ignored_tag_two']
        );
        $this->assertSame($expectedResult, $actualResult);
    }

    public function testCleanModeMatchingTag(): void
    {
        $expectedResult = new \stdClass();
        $this->frontendMock->expects(
            $this->once()
        )->method(
            'clean'
        )->with(
            \Zend_Cache::CLEANING_MODE_MATCHING_TAG,
            ['test_tag_one', 'test_tag_two', Config::CACHE_TAG]
        )->willReturn(
            $expectedResult
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
    public function testCleanModeMatchingAnyTag($fixtureResultOne, $fixtureResultTwo, $expectedResult): void
    {
        $this->frontendMock
            ->method('clean')
            ->withConsecutive(
                [\Zend_Cache::CLEANING_MODE_MATCHING_TAG, ['test_tag_one', Config::CACHE_TAG]],
                [\Zend_Cache::CLEANING_MODE_MATCHING_TAG, ['test_tag_two', Config::CACHE_TAG]]
            )->willReturnOnConsecutiveCalls($fixtureResultOne, $fixtureResultTwo);
        $actualResult = $this->model->clean(
            \Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG,
            ['test_tag_one', 'test_tag_two']
        );
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @return array
     */
    public function cleanModeMatchingAnyTagDataProvider(): array
    {
        return [
            'failure, failure' => [false, false, false],
            'failure, success' => [false, true, true],
            'success, failure' => [true, false, true],
            'success, success' => [true, true, true]
        ];
    }
}
