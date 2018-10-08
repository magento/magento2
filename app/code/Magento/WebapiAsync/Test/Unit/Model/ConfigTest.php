<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\WebapiAsync\Test\Unit\Model;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Webapi\Model\Cache\Type\Webapi;
use Magento\Webapi\Model\Config as WebapiConfig;
use Magento\WebapiAsync\Model\Config;
use Magento\Webapi\Model\Config\Converter;

class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Webapi|\PHPUnit_Framework_MockObject_MockObject
     */
    private $webapiCacheMock;

    /**
     * @var WebapiConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->webapiCacheMock = $this->createMock(\Magento\Webapi\Model\Cache\Type\Webapi::class);
        $this->configMock = $this->createMock(WebapiConfig::class);
        $this->serializerMock = $this->createMock(SerializerInterface::class);

        $this->config = $objectManager->getObject(
            Config::class,
            [
                'cache' => $this->webapiCacheMock,
                'webApiConfig' => $this->configMock,
                'serializer' => $this->serializerMock
            ]
        );
    }

    public function testGetServicesSetsTopicFromRoute()
    {
        $services = [
            Converter::KEY_ROUTES => [
                '/V1/products' => [
                    'POST' => [
                        'service' => [
                            'class' => 'Magento\Catalog\Api\ProductRepositoryInterface',
                            'method' => 'save',
                        ]
                    ]
                ]
            ]
        ];
        $this->configMock->expects($this->once())
            ->method('getServices')
            ->willReturn($services);

        /* example of what $this->config->getServices() returns
        $result = [
            'async.V1.products.POST' => [
                'interface' => 'Magento\Catalog\Api\ProductRepositoryInterface',
                'method' => 'save',
                'topic' => 'async.V1.products.POST',
            ]
        ];
        */
        $result = $this->config->getServices();

        $expectedTopic = 'async.V1.products.POST';
        $this->assertArrayHasKey($expectedTopic, $result);
        $this->assertEquals($result[$expectedTopic]['topic'], $expectedTopic);
    }
}
