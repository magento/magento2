<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Cache\Type;

use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\Cache\Type\AccessProxy;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\TestFramework\Unit\Helper\ProxyTesting;
use PHPUnit\Framework\TestCase;

class AccessProxyTest extends TestCase
{
    /**
     * @param string $method
     * @param array $params
     * @param bool $disabledResult
     * @param mixed $enabledResult
     * @dataProvider proxyMethodDataProvider
     */
    public function testProxyMethod($method, $params, $disabledResult, $enabledResult)
    {
        $identifier = 'cache_type_identifier';

        $frontendMock = $this->getMockForAbstractClass(FrontendInterface::class);

        $cacheEnabler = $this->getMockForAbstractClass(StateInterface::class);
        $cacheEnabler->expects($this->at(0))->method('isEnabled')->with($identifier)->willReturn(false);
        $cacheEnabler->expects($this->at(1))->method('isEnabled')->with($identifier)->willReturn(true);

        $object = new AccessProxy($frontendMock, $cacheEnabler, $identifier);
        $helper = new ProxyTesting();

        // For the first call the cache is disabled - so fake default result is returned
        $result = $helper->invokeWithExpectations($object, $frontendMock, $method, $params, $enabledResult);
        $this->assertSame($disabledResult, $result);

        // For the second call the cache is enabled - so real cache result is returned
        $result = $helper->invokeWithExpectations($object, $frontendMock, $method, $params, $enabledResult);
        $this->assertSame($enabledResult, $result);
    }

    /**
     * @return array
     */
    public static function proxyMethodDataProvider()
    {
        return [
            ['test', ['record_id'], false, 111],
            ['load', ['record_id'], false, '111'],
            ['save', ['record_value', 'record_id', ['tag'], 555], true, false],
            ['remove', ['record_id'], true, false],
            ['clean', [\Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, ['tag']], true, false]
        ];
    }
}
