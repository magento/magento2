<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Cache\Test\Unit\Frontend\Adapter;

class ZendTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $method
     * @param array $params
     * @param array $expectedParams
     * @param mixed $expectedResult
     * @dataProvider proxyMethodDataProvider
     */
    public function testProxyMethod($method, $params, $expectedParams, $expectedResult)
    {
        $frontendMock = $this->getMock('Zend_Cache_Core');
        $object = new \Magento\Framework\Cache\Frontend\Adapter\Zend($frontendMock);
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ProxyTesting();
        $result = $helper->invokeWithExpectations(
            $object,
            $frontendMock,
            $method,
            $params,
            $expectedResult,
            $method,
            $expectedParams
        );
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function proxyMethodDataProvider()
    {
        return [
            'test' => ['test', ['record_id'], ['RECORD_ID'], 111],
            'load' => ['load', ['record_id'], ['RECORD_ID'], '111'],
            'save' => [
                'save',
                ['record_value', 'record_id', ['tag1', 'tag2'], 555],
                ['record_value', 'RECORD_ID', ['TAG1', 'TAG2'], 555],
                true,
            ],
            'remove' => ['remove', ['record_id'], ['RECORD_ID'], true],
            'clean mode "all"' => [
                'clean',
                [\Zend_Cache::CLEANING_MODE_ALL, []],
                [\Zend_Cache::CLEANING_MODE_ALL, []],
                true,
            ],
            'clean mode "matching tag"' => [
                'clean',
                [\Zend_Cache::CLEANING_MODE_MATCHING_TAG, ['tag1', 'tag2']],
                [\Zend_Cache::CLEANING_MODE_MATCHING_TAG, ['TAG1', 'TAG2']],
                true,
            ],
            'clean mode "matching any tag"' => [
                'clean',
                [\Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, ['tag1', 'tag2']],
                [\Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, ['TAG1', 'TAG2']],
                true,
            ],
            'getBackend' => [
                'getBackend',
                [],
                [],
                $this->getMock('Zend_Cache_Backend'),
            ]
        ];
    }

    /**
     * @param string $cleaningMode
     * @param string $expectedErrorMessage
     * @dataProvider cleanExceptionDataProvider
     */
    public function testCleanException($cleaningMode, $expectedErrorMessage)
    {
        $this->setExpectedException('InvalidArgumentException', $expectedErrorMessage);
        $object = new \Magento\Framework\Cache\Frontend\Adapter\Zend($this->getMock('Zend_Cache_Core'));
        $object->clean($cleaningMode);
    }

    /**
     * @return array
     */
    public function cleanExceptionDataProvider()
    {
        return [
            'cleaning mode "expired"' => [
                \Zend_Cache::CLEANING_MODE_OLD,
                "Magento cache frontend does not support the cleaning mode 'old'.",
            ],
            'cleaning mode "not matching tag"' => [
                \Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG,
                "Magento cache frontend does not support the cleaning mode 'notMatchingTag'.",
            ],
            'non-existing cleaning mode' => [
                'nonExisting',
                "Magento cache frontend does not support the cleaning mode 'nonExisting'.",
            ]
        ];
    }

    public function testGetLowLevelFrontend()
    {
        $frontendMock = $this->getMock('Zend_Cache_Core');
        $object = new \Magento\Framework\Cache\Frontend\Adapter\Zend($frontendMock);
        $this->assertSame($frontendMock, $object->getLowLevelFrontend());
    }
}
