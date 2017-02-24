<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Cache\Test\Unit\Frontend\Decorator;

class BareTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $method
     * @param array $params
     * @param mixed $expectedResult
     * @dataProvider proxyMethodDataProvider
     */
    public function testProxyMethod($method, $params, $expectedResult)
    {
        $frontendMock = $this->getMock(\Magento\Framework\Cache\FrontendInterface::class);

        $object = new \Magento\Framework\Cache\Frontend\Decorator\Bare($frontendMock);
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ProxyTesting();
        $result = $helper->invokeWithExpectations($object, $frontendMock, $method, $params, $expectedResult);
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
            ['save', ['record_value', 'record_id', ['tag'], 555], true],
            ['remove', ['record_id'], true],
            ['clean', [\Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, ['tag']], true],
            ['getBackend', [], $this->getMock(\Zend_Cache_Backend::class)],
            ['getLowLevelFrontend', [], $this->getMock(\Zend_Cache_Core::class)],
        ];
    }
}
