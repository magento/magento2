<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\ResourceConnection;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Config
     */
    private $config;

    /**
     * @var \Magento\Framework\Config\ScopeInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeMock;

    /**
     * @var \Magento\Framework\Config\CacheInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheMock;

    /**
     * @var \Magento\Framework\App\ResourceConnection\Config\Reader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $readerMock;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    /**
     * @var array
     */
    private $resourcesConfig;

    /**
     * @var array
     */
    private $initialResources;

    protected function setUp()
    {
        $this->scopeMock = $this->getMock(\Magento\Framework\Config\ScopeInterface::class);
        $this->cacheMock = $this->getMock(\Magento\Framework\Config\CacheInterface::class);

        $this->readerMock = $this->getMock(
            \Magento\Framework\App\ResourceConnection\Config\Reader::class,
            [],
            [],
            '',
            false
        );
        $this->serializerMock = $this->getMock(\Magento\Framework\Serialize\SerializerInterface::class);

        $this->resourcesConfig = [
            'mainResourceName' => ['name' => 'mainResourceName', 'extends' => 'anotherResourceName'],
            'otherResourceName' => ['name' => 'otherResourceName', 'connection' => 'otherConnectionName'],
            'anotherResourceName' => ['name' => 'anotherResourceName', 'connection' => 'anotherConnection'],
            'brokenResourceName' => ['name' => 'brokenResourceName', 'extends' => 'absentResourceName'],
            'extendedResourceName' => ['name' => 'extendedResourceName', 'extends' => 'validResource'],
        ];

        $this->initialResources = [
            'validResource' => ['connection' => 'validConnectionName'],
        ];

        $serializedData = 'serialized data';
        $this->cacheMock->expects($this->any())
            ->method('load')
            ->willReturn($serializedData);
        $this->serializerMock->method('unserialize')
            ->with($serializedData)
            ->willReturn($this->resourcesConfig);

        /**
         * @var \Magento\Framework\App\DeploymentConfig|\PHPUnit_Framework_MockObject_MockObject $deploymentConfigMock
         */
        $deploymentConfigMock = $this->getMock(\Magento\Framework\App\DeploymentConfig::class, [], [], '', false);
        $deploymentConfigMock->expects($this->once())
            ->method('getConfigData')
            ->with('resource')
            ->willReturn($this->initialResources);

        $this->config = new \Magento\Framework\App\ResourceConnection\Config(
            $this->readerMock,
            $this->scopeMock,
            $this->cacheMock,
            $deploymentConfigMock,
            'cacheId',
            $this->serializerMock
        );
    }

    /**
     * @param string $resourceName
     * @param string $connectionName
     * @dataProvider getConnectionNameDataProvider
     */
    public function testGetConnectionName($resourceName, $connectionName)
    {
        $this->assertEquals($connectionName, $this->config->getConnectionName($resourceName));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testExceptionConstructor()
    {
        $deploymentConfigMock = $this->getMock(\Magento\Framework\App\DeploymentConfig::class, [], [], '', false);
        $deploymentConfigMock->expects($this->once())
            ->method('getConfigData')
            ->with('resource')
            ->willReturn(['validResource' => ['somekey' => 'validConnectionName']]);

        new \Magento\Framework\App\ResourceConnection\Config(
            $this->readerMock,
            $this->scopeMock,
            $this->cacheMock,
            $deploymentConfigMock,
            'cacheId',
            $this->serializerMock
        );
    }

    /**
     * @return array
     */
    public function getConnectionNameDataProvider()
    {
        return [
            ['resourceName' => 'otherResourceName', 'connectionName' => 'otherConnectionName'],
            ['resourceName' => 'mainResourceName', 'connectionName' => 'anotherConnection'],
            [
                'resourceName' => 'brokenResourceName',
                'connectionName' => \Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION
            ],
            ['resourceName' => 'extendedResourceName', 'connectionName' => 'validConnectionName'],
            ['resourceName' => 'validResource', 'connectionName' => 'validConnectionName']
        ];
    }
}
