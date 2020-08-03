<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\WebapiAsync\Test\Unit\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Webapi\Model\Cache\Type\Webapi;
use Magento\Webapi\Model\Config as WebapiConfig;
use Magento\Webapi\Model\Config\Converter;
use Magento\WebapiAsync\Model\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Webapi|MockObject
     */
    private $webapiCacheMock;

    /**
     * @var WebapiConfig|MockObject
     */
    private $configMock;

    /**
     * @var SerializerInterface|MockObject
     */
    private $serializerMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->webapiCacheMock = $this->createMock(Webapi::class);
        $this->configMock = $this->createMock(WebapiConfig::class);
        $this->serializerMock = $this->getMockForAbstractClass(SerializerInterface::class);

        $this->config = $objectManager->getObject(
            Config::class,
            [
                'cache' => $this->webapiCacheMock,
                'webApiConfig' => $this->configMock,
                'serializer' => $this->serializerMock
            ]
        );
    }

    public function testGetServicesSetsTopicFromServiceContractName()
    {
        $services = [
            Converter::KEY_ROUTES => [
                '/V1/products' => [
                    'POST' => [
                        'service' => [
                            'class' => ProductRepositoryInterface::class,
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
                'topic' => 'async.magento.catalog.api.productrepositoryinterface.save.post',
            ]
        ];
        */
        $result = $this->config->getServices();

        $expectedTopic = 'async.magento.catalog.api.productrepositoryinterface.save.post';
        $lookupKey = 'async.V1.products.POST';
        $this->assertArrayHasKey($lookupKey, $result);
        $this->assertEquals($result[$lookupKey]['topic'], $expectedTopic);
    }
}
