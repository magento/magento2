<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Test\Unit\Model;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Integration\Model\IntegrationConfig;
use Magento\Integration\Model\Cache\TypeIntegration;

/**
 * Unit test for \Magento\Integration\Model\IntegrationConfig
 */
class IntegrationConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IntegrationConfig
     */
    private $integrationConfigModel;

    /**
     * @var TypeIntegration|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configCacheTypeMock;

    /**
     * @var  \Magento\Integration\Model\Config\Integration\Reader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configReaderMock;

    /**
     * @var SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializer;

    protected function setUp()
    {
        $this->configCacheTypeMock = $this->getMockBuilder(\Magento\Integration\Model\Cache\TypeIntegration::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configReaderMock = $this->getMockBuilder(\Magento\Integration\Model\Config\Integration\Reader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->serializer = $this->getMockBuilder(SerializerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
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
            ->will($this->returnValue($serializedIntegrations));
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
            ->will($this->returnValue(null));
        $this->configReaderMock->expects($this->once())
            ->method('read')
            ->will($this->returnValue($integrations));
        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($integrations)
            ->willReturn($serializedIntegrations);
        $this->configCacheTypeMock->expects($this->once())
            ->method('save')
            ->with($serializedIntegrations, IntegrationConfig::CACHE_ID, [TypeIntegration::CACHE_TAG])
            ->will($this->returnValue(null));

        $this->assertEquals($integrations, $this->integrationConfigModel->getIntegrations());
    }
}
