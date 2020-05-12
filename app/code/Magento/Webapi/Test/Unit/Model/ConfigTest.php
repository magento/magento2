<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Webapi\Test\Unit\Model;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Webapi\Model\Cache\Type\Webapi;
use Magento\Webapi\Model\Config;
use Magento\Webapi\Model\Config\Reader;
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
     * @var Reader|MockObject
     */
    private $configReaderMock;

    /**
     * @var SerializerInterface|MockObject
     */
    private $serializerMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->webapiCacheMock = $this->createMock(Webapi::class);
        $this->configReaderMock = $this->createMock(Reader::class);
        $this->serializerMock = $this->getMockForAbstractClass(SerializerInterface::class);

        $this->config = $objectManager->getObject(
            Config::class,
            [
                'cache' => $this->webapiCacheMock,
                'configReader' => $this->configReaderMock,
                'serializer' => $this->serializerMock
            ]
        );
    }

    public function testGetServices()
    {
        $data = ['foo' => 'bar'];
        $serializedData = 'serialized data';
        $this->webapiCacheMock->expects($this->once())
            ->method('load')
            ->with(Config::CACHE_ID)
            ->willReturn($serializedData);
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with($serializedData)
            ->willReturn($data);
        $this->config->getServices();
        $this->assertEquals($data, $this->config->getServices());
    }

    public function testGetServicesNoCache()
    {
        $data = ['foo' => 'bar'];
        $serializedData = 'serialized data';
        $this->webapiCacheMock->expects($this->once())
            ->method('load')
            ->with(Config::CACHE_ID)
            ->willReturn(false);
        $this->serializerMock->expects($this->never())
            ->method('unserialize');
        $this->configReaderMock->expects($this->once())
            ->method('read')
            ->willReturn($data);
        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with($data)
            ->willReturn($serializedData);
        $this->webapiCacheMock->expects($this->once())
            ->method('save')
            ->with(
                $serializedData,
                Config::CACHE_ID
            );

        $this->config->getServices();
        $this->assertEquals($data, $this->config->getServices());
    }
}
