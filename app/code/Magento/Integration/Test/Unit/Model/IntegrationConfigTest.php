<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Test\Unit\Model;

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
    protected $integrationConfigModel;

    /**
     * @var TypeIntegration|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configCacheTypeMock;

    /**
     * @var  \Magento\Integration\Model\Config\Integration\Reader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configReaderMock;

    protected function setUp()
    {
        $this->configCacheTypeMock = $this->getMockBuilder('Magento\Integration\Model\Cache\TypeIntegration')
            ->disableOriginalConstructor()
            ->getMock();
        $this->configReaderMock = $this->getMockBuilder('Magento\Integration\Model\Config\Integration\Reader')
            ->disableOriginalConstructor()
            ->getMock();
        $this->integrationConfigModel = new IntegrationConfig(
            $this->configCacheTypeMock,
            $this->configReaderMock
        );
    }

    public function testGetIntegrationsFromConfigCacheType()
    {
        $integrations = ['foo', 'bar', 'baz'];
        $this->configCacheTypeMock->expects($this->once())
            ->method('load')
            ->with(IntegrationConfig::CACHE_ID)
            ->will($this->returnValue(serialize($integrations)));

        $this->assertEquals($integrations, $this->integrationConfigModel->getIntegrations());
    }

    public function testGetIntegrationsFromConfigReader()
    {
        $integrations = ['foo', 'bar', 'baz'];
        $this->configCacheTypeMock->expects($this->once())
            ->method('load')
            ->with(IntegrationConfig::CACHE_ID)
            ->will($this->returnValue(null));
        $this->configCacheTypeMock->expects($this->once())
            ->method('save')
            ->with(serialize($integrations), IntegrationConfig::CACHE_ID, [TypeIntegration::CACHE_TAG])
            ->will($this->returnValue(null));
        $this->configReaderMock->expects($this->once())
            ->method('read')
            ->will($this->returnValue($integrations));

        $this->assertEquals($integrations, $this->integrationConfigModel->getIntegrations());
    }
}
