<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Test\Unit\Model;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Integration\Model\IntegrationConfig;
use Magento\Integration\Model\Cache\TypeIntegration;

/**
 * Unit test for \Magento\Integration\Model\IntegrationConfig
 */
class IntegrationConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var IntegrationConfig
     */
    private $integrationConfigModel;

    /**
     * @var TypeIntegration|\PHPUnit\Framework\MockObject\MockObject
     */
    private $configCacheTypeMock;

    /**
     * @var  \Magento\Integration\Model\Config\Integration\Reader|\PHPUnit\Framework\MockObject\MockObject
     */
    private $configReaderMock;

    /**
     * @var SerializerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $serializer;

    protected function setUp(): void
    {
        $this->configCacheTypeMock = $this->getMockBuilder(\Magento\Integration\Model\Cache\TypeIntegration::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configReaderMock = $this->getMockBuilder(\Magento\Integration\Model\Config\Integration\Reader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->serializer = $this->getMockBuilder(SerializerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->integrationConfigModel = new IntegrationConfig(
            $this->configCacheTypeMock,
            $this->configReaderMock,
            $this->serializer
        );
    }

    public function testGetIntegrationsFromConfigCacheType()
    {
        $integrations = ['foo', 'bar', 'baz'];
        $serializedIntegrations = '["foo","bar","baz"]';
        $this->configCacheTypeMock->expects($this->once())
            ->method('load')
            ->with(IntegrationConfig::CACHE_ID)
            ->willReturn($serializedIntegrations);
        $this->serializer->expects($this->once())
            ->method('unserialize')
            ->with($serializedIntegrations)
            ->willReturn($integrations);

        $this->assertEquals($integrations, $this->integrationConfigModel->getIntegrations());
    }

    public function testGetIntegrationsFromConfigReader()
    {
        $integrations = ['foo', 'bar', 'baz'];
        $serializedIntegrations = '["foo","bar","baz"]';
        $this->configCacheTypeMock->expects($this->once())
            ->method('load')
            ->with(IntegrationConfig::CACHE_ID)
            ->willReturn(null);
        $this->configReaderMock->expects($this->once())
            ->method('read')
            ->willReturn($integrations);
        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($integrations)
            ->willReturn($serializedIntegrations);
        $this->configCacheTypeMock->expects($this->once())
            ->method('save')
            ->with($serializedIntegrations, IntegrationConfig::CACHE_ID, [TypeIntegration::CACHE_TAG])
            ->willReturn(null);

        $this->assertEquals($integrations, $this->integrationConfigModel->getIntegrations());
    }
}
