<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Test\Unit\Frontend\Decorator;

use Magento\Framework\Cache\Frontend\Decorator\Bare;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\TestFramework\Unit\Helper\ProxyTesting;
use PHPUnit\Framework\TestCase;

class BareTest extends TestCase
{
    /**
     * @param string $method
     * @param array $params
     * @param mixed $expectedResult
     * @dataProvider proxyMethodDataProvider
     */
    public function testProxyMethod($method, $params, $expectedResult)
    {
        if (is_callable($expectedResult)) {
            $expectedResult = $expectedResult($this);
        }
        $frontendMock = $this->getMockForAbstractClass(FrontendInterface::class);

        $object = new Bare($frontendMock);
        $helper = new ProxyTesting();
        $result = $helper->invokeWithExpectations($object, $frontendMock, $method, $params, $expectedResult);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @return array
     */
    public static function proxyMethodDataProvider()
    {
        return [
            ['test', ['record_id'], 111],
            ['load', ['record_id'], '111'],
            ['save', ['record_value', 'record_id', ['tag'], 555], true],
            ['remove', ['record_id'], true],
            ['clean', [\Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, ['tag']], true],
            ['getBackend', [], static fn (self $testCase) => $testCase->createZendCacheBackendMock()],
            ['getLowLevelFrontend', [], static fn (self $testCase) => $testCase->createZendCacheCoreMock()],
        ];
    }

    public function createZendCacheBackendMock()
    {
        return $this->createMock(\Zend_Cache_Backend::class);
    }

    public function createZendCacheCoreMock()
    {
        return $this->createMock(\Zend_Cache_Core::class);
    }
}
