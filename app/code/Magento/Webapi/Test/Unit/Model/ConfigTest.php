<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Test\Unit\Model;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Webapi\Model\Config;
use Magento\Webapi\Model\Config\Reader;
use Magento\Webapi\Model\Cache\Type\Webapi;

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
     * @var Reader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configReaderMock;

    /**
     * @var SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->webapiCacheMock = $this->createMock(\Magento\Webapi\Model\Cache\Type\Webapi::class);
        $this->configReaderMock = $this->createMock(\Magento\Webapi\Model\Config\Reader::class);
        $this->serializerMock = $this->createMock(SerializerInterface::class);

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
