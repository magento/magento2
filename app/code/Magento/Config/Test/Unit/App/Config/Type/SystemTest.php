<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Config\Test\Unit\App\Config\Type;

use Magento\Config\App\Config\Type\System;
use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Framework\App\Config\Spi\PostProcessorInterface;
use Magento\Framework\App\Config\Spi\PreProcessorInterface;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\Config\Processor\Fallback;

/**
 * Test how Class process source, cache them and retrieve value by path
 * @package Magento\Config\Test\Unit\App\Config\Type
 */
class SystemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigSourceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $source;

    /**
     * @var PostProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $postProcessor;

    /**
     * @var PreProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $preProcessor;

    /**
     * @var Fallback|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fallback;

    /**
     * @var FrontendInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cache;

    /**
     * @var System
     */
    private $configType;

    /**
     * @var SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializer;

    public function setUp()
    {
        $this->source = $this->getMockBuilder(ConfigSourceInterface::class)
            ->getMockForAbstractClass();
        $this->postProcessor = $this->getMockBuilder(PostProcessorInterface::class)
            ->getMockForAbstractClass();
        $this->fallback = $this->getMockBuilder(Fallback::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cache = $this->getMockBuilder(FrontendInterface::class)
            ->getMockForAbstractClass();
        $this->preProcessor = $this->getMockBuilder(PreProcessorInterface::class)
            ->getMockForAbstractClass();
        $this->serializer = $this->getMockBuilder(SerializerInterface::class)
            ->getMock();

        $this->configType = new System(
            $this->source,
            $this->postProcessor,
            $this->fallback,
            $this->cache,
            $this->serializer,
            $this->preProcessor
        );
    }

    public function testGetCached()
    {
        $path = 'default/dev/unsecure/url';
        $url = 'http://magento.test/';
        $data = [
            'unsecure' => [
                'url' => $url
            ]
        ];

        $this->cache->expects($this->any())
            ->method('load')
            ->withConsecutive(['system_CACHE_EXISTS'], ['system_defaultdev'])
            ->willReturnOnConsecutiveCalls('1', serialize($data));
        $this->serializer->expects($this->once())
            ->method('unserialize')
            ->willReturn($data);
        $this->assertEquals($url, $this->configType->get($path));
    }

    public function testGetNotCached()
    {
        $path = 'default/dev/unsecure/url';
        $url = 'http://magento.test/';
        $data = [
            'default' => [
                'dev' => [
                    'unsecure' => [
                        'url' => $url
                    ]
                ]
            ]
        ];

        $dataToCache = [
            'unsecure' => [
                'url' => $url
            ]
        ];
        $this->cache->expects($this->any())
            ->method('load')
            ->withConsecutive(['system_CACHE_EXISTS'], ['system_defaultdev'])
            ->willReturnOnConsecutiveCalls(false, false);

        $this->serializer->expects($this->once())
            ->method('serialize')
            ->willReturn(serialize($dataToCache));
        $this->source->expects($this->once())
            ->method('get')
            ->willReturn($data);
        $this->fallback->expects($this->once())
            ->method('process')
            ->with($data)
            ->willReturnArgument(0);
        $this->preProcessor->expects($this->once())
            ->method('process')
            ->with($data)
            ->willReturnArgument(0);
        $this->postProcessor->expects($this->once())
            ->method('process')
            ->with($data)
            ->willReturnArgument(0);
        $this->cache->expects($this->any())
            ->method('save')
            ->withConsecutive(
                [
                    serialize($dataToCache),
                    'system_defaultdev',
                    [System::CACHE_TAG]
                ],
                [
                    'system_CACHE_EXISTS',
                    'system_CACHE_EXISTS',
                    [System::CACHE_TAG]
                ]
            );

        $this->assertEquals($url, $this->configType->get($path));
    }
}
