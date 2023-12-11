<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Integration\Test\Unit\Model;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Integration\Model\Cache\TypeConsolidated as Type;
use Magento\Integration\Model\Config\Consolidated\Reader;
use Magento\Integration\Model\ConsolidatedConfig as Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for \Magento\Integration\Model\ConsolidatedConfig
 */
class ConsolidatedConfigTest extends TestCase
{
    /**
     * Integration config model
     *
     * @var Config
     */
    private $configModel;

    /**
     * @var Type|MockObject
     */
    private $configCacheTypeMock;

    /**
     * @var  Reader|MockObject
     */
    private $configReaderMock;

    /**
     * @var  SerializerInterface|MockObject
     */
    private $serializer;

    protected function setUp(): void
    {
        $this->configCacheTypeMock = $this->getMockBuilder(\Magento\Integration\Model\Cache\TypeConsolidated::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configReaderMock = $this->getMockBuilder(Reader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->serializer = $this->getMockBuilder(SerializerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $objectManagerHelper = new ObjectManager($this);
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
            ->willReturn($serializedIntegrations);
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
            ->with($serializedIntegrations, Config::CACHE_ID, [Type::CACHE_TAG]);

        $this->assertEquals($integrations, $this->configModel->getIntegrations());
    }
}
