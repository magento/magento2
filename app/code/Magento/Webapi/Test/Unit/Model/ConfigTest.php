<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Webapi\Test\Unit\Model;

use Magento\Framework\App\ObjectManager\ConfigWriterInterface;
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

    /**
     * @var ConfigWriterInterface|MockObject
     */
    private $configWriterMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->webapiCacheMock = $this->createMock(Webapi::class);
        $this->configReaderMock = $this->createMock(Reader::class);
        $this->serializerMock = $this->getMockForAbstractClass(SerializerInterface::class);
        $this->configWriterMock = $this->getMockForAbstractClass(ConfigWriterInterface::class);

        $this->config = $objectManager->getObject(
            Config::class,
            [
                'cache' => $this->webapiCacheMock,
                'configReader' => $this->configReaderMock,
                'serializer' => $this->serializerMock,
                'configWriter' => null
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

    public function testGetServicesFromCompiledConfig()
    {
        $data = ['foo' => 'bar'];

        /** @var Config|MockObject $config */
        $config = $this->getMockBuilder(Config::class)
            ->setConstructorArgs([
                'cache' => $this->webapiCacheMock,
                'configReader' => $this->configReaderMock,
                'serializer' => $this->serializerMock,
                'configWriter' => $this->configWriterMock
            ])
            ->onlyMethods(['isCompiledConfigAvailable', 'loadCompiledConfig'])
            ->getMock();

        $config->expects($this->once())
            ->method('isCompiledConfigAvailable')
            ->with(Config::CACHE_ID)
            ->willReturn(true);
        $config->expects($this->once())
            ->method('loadCompiledConfig')
            ->with(Config::CACHE_ID)
            ->willReturn($data);

        $this->webapiCacheMock->expects($this->never())
            ->method('load');
        $this->configReaderMock->expects($this->never())
            ->method('read');

        $this->assertEquals($data, $config->getServices());
    }

    public function testGetServicesCompiledConfigNotAvailableFallsBackToCache()
    {
        $data = ['foo' => 'bar'];
        $serializedData = 'serialized data';

        /** @var Config|MockObject $config */
        $config = $this->getMockBuilder(Config::class)
            ->setConstructorArgs([
                'cache' => $this->webapiCacheMock,
                'configReader' => $this->configReaderMock,
                'serializer' => $this->serializerMock,
                'configWriter' => $this->configWriterMock
            ])
            ->onlyMethods(['isCompiledConfigAvailable', 'loadCompiledConfig'])
            ->getMock();

        $config->expects($this->once())
            ->method('isCompiledConfigAvailable')
            ->with(Config::CACHE_ID)
            ->willReturn(false);
        $config->expects($this->never())
            ->method('loadCompiledConfig');

        $this->webapiCacheMock->expects($this->once())
            ->method('load')
            ->with(Config::CACHE_ID)
            ->willReturn($serializedData);
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with($serializedData)
            ->willReturn($data);

        $this->assertEquals($data, $config->getServices());
    }

    public function testGetServicesWritesCompiledConfigOnCacheMiss()
    {
        $data = ['foo' => 'bar'];
        $serializedData = 'serialized data';

        /** @var Config|MockObject $config */
        $config = $this->getMockBuilder(Config::class)
            ->setConstructorArgs([
                'cache' => $this->webapiCacheMock,
                'configReader' => $this->configReaderMock,
                'serializer' => $this->serializerMock,
                'configWriter' => $this->configWriterMock
            ])
            ->onlyMethods(['isCompiledConfigAvailable'])
            ->getMock();

        $config->expects($this->once())
            ->method('isCompiledConfigAvailable')
            ->with(Config::CACHE_ID)
            ->willReturn(false);

        $this->webapiCacheMock->expects($this->once())
            ->method('load')
            ->with(Config::CACHE_ID)
            ->willReturn(false);
        $this->configReaderMock->expects($this->once())
            ->method('read')
            ->willReturn($data);
        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with($data)
            ->willReturn($serializedData);
        $this->webapiCacheMock->expects($this->once())
            ->method('save')
            ->with($serializedData, Config::CACHE_ID);
        $this->configWriterMock->expects($this->once())
            ->method('write')
            ->with(Config::CACHE_ID, $data);

        $this->assertEquals($data, $config->getServices());
    }

    public function testGetServicesWritesCompiledConfigOnCacheHit()
    {
        $data = ['foo' => 'bar'];
        $serializedData = 'serialized data';

        /** @var Config|MockObject $config */
        $config = $this->getMockBuilder(Config::class)
            ->setConstructorArgs([
                'cache' => $this->webapiCacheMock,
                'configReader' => $this->configReaderMock,
                'serializer' => $this->serializerMock,
                'configWriter' => $this->configWriterMock
            ])
            ->onlyMethods(['isCompiledConfigAvailable'])
            ->getMock();

        $config->expects($this->once())
            ->method('isCompiledConfigAvailable')
            ->with(Config::CACHE_ID)
            ->willReturn(false);

        $this->webapiCacheMock->expects($this->once())
            ->method('load')
            ->with(Config::CACHE_ID)
            ->willReturn($serializedData);
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with($serializedData)
            ->willReturn($data);
        $this->configReaderMock->expects($this->never())
            ->method('read');
        $this->configWriterMock->expects($this->once())
            ->method('write')
            ->with(Config::CACHE_ID, $data);

        $this->assertEquals($data, $config->getServices());
    }

    public function testGetServicesNoCacheMissWithoutConfigWriter()
    {
        $objectManager = new ObjectManager($this);
        $data = ['foo' => 'bar'];
        $serializedData = 'serialized data';

        /** @var Config $configWithoutWriter */
        $configWithoutWriter = $objectManager->getObject(
            Config::class,
            [
                'cache' => $this->webapiCacheMock,
                'configReader' => $this->configReaderMock,
                'serializer' => $this->serializerMock,
                'configWriter' => null
            ]
        );

        $this->webapiCacheMock->expects($this->once())
            ->method('load')
            ->with(Config::CACHE_ID)
            ->willReturn(false);
        $this->configReaderMock->expects($this->once())
            ->method('read')
            ->willReturn($data);
        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with($data)
            ->willReturn($serializedData);
        $this->webapiCacheMock->expects($this->once())
            ->method('save')
            ->with($serializedData, Config::CACHE_ID);

        // configWriter is null, so write should never be called
        $this->configWriterMock->expects($this->never())
            ->method('write');

        $this->assertEquals($data, $configWithoutWriter->getServices());
    }
}
