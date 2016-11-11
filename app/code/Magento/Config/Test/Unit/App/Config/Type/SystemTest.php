<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\App\Config\Type;

use Magento\Config\App\Config\Type\System;
use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Framework\App\Config\Spi\PostProcessorInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Serialize\Serializer\Serialize;
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
     * @var Serialize|\PHPUnit_Framework_MockObject_MockObject
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
        $this->serializer = $this->getMockBuilder(Serialize::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configType = new System(
            $this->source,
            $this->postProcessor,
            $this->fallback,
            $this->cache,
            $this->serializer
        );
    }

    /**
     * @param bool $isCached
     * @dataProvider getDataProvider
     */
    public function testGet($isCached)
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

        $this->cache->expects($this->once())
            ->method('load')
            ->with(System::CONFIG_TYPE)
            ->willReturn($isCached ? $data : null);

        if ($isCached) {
            $this->serializer->expects($this->once())
                ->method('unserialize')
                ->willReturn($data);
        }

        if (!$isCached) {
            $this->serializer->expects($this->once())
                ->method('serialize')
                ->willReturn(serialize($data));
            $this->source->expects($this->once())
                ->method('get')
                ->willReturn($data);
            $this->fallback->expects($this->once())
                ->method('process')
                ->with($data)
                ->willReturnArgument(0);
            $this->postProcessor->expects($this->once())
                ->method('process')
                ->with($data)
                ->willReturnArgument(0);
            $this->cache->expects($this->once())
                ->method('save')
                ->with(
                    serialize($data),
                    System::CONFIG_TYPE,
                    [System::CACHE_TAG]
                );
        }

        $this->assertEquals($url, $this->configType->get($path));
    }

    /**
     * @return array
     */
    public function getDataProvider()
    {
        return [
            [true],
            [false]
        ];
    }
}
