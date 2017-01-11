<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Test\Unit\Model;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Integration\Model\ConsolidatedConfig as Config;
use Magento\Integration\Model\Cache\TypeConsolidated as Type;

/**
 * Unit test for \Magento\Integration\Model\ConsolidatedConfig
 */
class ConsolidatedConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Integration config model
     *
     * @var Config
     */
    private $configModel;

    /**
     * @var Type|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configCacheTypeMock;

    /**
     * @var  \Magento\Integration\Model\Config\Consolidated\Reader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configReaderMock;

    /**
     * @var  SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializer;

    protected function setUp()
    {
        $this->configCacheTypeMock = $this->getMockBuilder(\Magento\Integration\Model\Cache\TypeConsolidated::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configReaderMock = $this->getMockBuilder(\Magento\Integration\Model\Config\Consolidated\Reader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->serializer = $this->getMockBuilder(SerializerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->configModel = $objectManagerHelper->getObject(
            \Magento\Integration\Model\ConsolidatedConfig::class,
            [
                'configCacheType' => $this->configCacheTypeMock,
                'configReader' => $this->configReaderMock,
                'serializer' => $this->serializer,
            ]
        );
    }

    public function testGetIntegrationsFromConfigCacheType()
    {
        $integrations = ['foo', 'bar', 'baz'];
        $serializedIntegrations = '["foo","bar","baz"]';
        $this->configCacheTypeMock->expects($this->once())
            ->method('load')
            ->with(Config::CACHE_ID)
            ->will($this->returnValue($serializedIntegrations));
        $this->serializer->expects($this->once())
            ->method('unserialize')
            ->with($serializedIntegrations)
            ->willReturn($integrations);

        $this->assertEquals($integrations, $this->configModel->getIntegrations());
    }

    public function testGetIntegrationsFromConfigReader()
    {
        $integrations = ['foo', 'bar', 'baz'];
        $serializedIntegrations = '["foo","bar","baz"]';
        $this->configCacheTypeMock->expects($this->once())
            ->method('load')
            ->with(Config::CACHE_ID)
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
            ->with($serializedIntegrations, Config::CACHE_ID, [Type::CACHE_TAG]);

        $this->assertEquals($integrations, $this->configModel->getIntegrations());
    }
}
