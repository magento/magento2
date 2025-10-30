<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\ResourceConnection;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ResourceConnection\Config;
use Magento\Framework\App\ResourceConnection\Config\Reader;
use Magento\Framework\Config\CacheInterface;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Config\ScopeInterface;
use Magento\Framework\Serialize\SerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var ScopeInterface|MockObject
     */
    private $scopeMock;

    /**
     * @var CacheInterface|MockObject
     */
    private $cacheMock;

    /**
     * @var Reader|MockObject
     */
    private $readerMock;

    /**
     * @var SerializerInterface|MockObject
     */
    private $serializerMock;

    /**
     * @var array
     */
    private $resourcesConfig;

    /**
     * @var DeploymentConfig|MockObject
     */
    private $deploymentConfig;

    protected function setUp(): void
    {
        $this->scopeMock = $this->getMockForAbstractClass(ScopeInterface::class);
        $this->cacheMock = $this->getMockForAbstractClass(CacheInterface::class);

        $this->readerMock = $this->createMock(Reader::class);
        $this->serializerMock = $this->getMockForAbstractClass(SerializerInterface::class);

        $this->resourcesConfig = [
            'mainResourceName' => ['name' => 'mainResourceName', 'extends' => 'anotherResourceName'],
            'otherResourceName' => ['name' => 'otherResourceName', 'connection' => 'otherConnectionName'],
            'anotherResourceName' => ['name' => 'anotherResourceName', 'connection' => 'anotherConnection'],
            'brokenResourceName' => ['name' => 'brokenResourceName', 'extends' => 'absentResourceName'],
            'extendedResourceName' => ['name' => 'extendedResourceName', 'extends' => 'validResource'],
        ];

        $serializedData = 'serialized data';
        $this->cacheMock->expects($this->any())
            ->method('load')
            ->willReturn($serializedData);
        $this->serializerMock->method('unserialize')
            ->with($serializedData)
            ->willReturn($this->resourcesConfig);

        $this->deploymentConfig = $this->createMock(DeploymentConfig::class);
        $this->config = new Config(
            $this->readerMock,
            $this->scopeMock,
            $this->cacheMock,
            $this->deploymentConfig,
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
        $this->deploymentConfig->expects($this->once())
            ->method('getConfigData')
            ->with(ConfigOptionsListConstants::KEY_RESOURCE)
            ->willReturn([
                'validResource' => ['connection' => 'validConnectionName'],
            ]);
        $this->assertEquals($connectionName, $this->config->getConnectionName($resourceName));
    }

    public function testGetConnectionNameWithException()
    {
        $this->expectException('InvalidArgumentException');
        $deploymentConfigMock = $this->createMock(DeploymentConfig::class);
        $deploymentConfigMock->expects($this->once())
            ->method('getConfigData')
            ->with(ConfigOptionsListConstants::KEY_RESOURCE)
            ->willReturn(['validResource' => ['somekey' => 'validConnectionName']]);

        $config = new Config(
            $this->readerMock,
            $this->scopeMock,
            $this->cacheMock,
            $deploymentConfigMock,
            'cacheId',
            $this->serializerMock
        );
        $config->getConnectionName('default');
    }

    /**
     * @return array
     */
    public static function getConnectionNameDataProvider()
    {
        return [
            ['resourceName' => 'otherResourceName', 'connectionName' => 'otherConnectionName'],
            ['resourceName' => 'mainResourceName', 'connectionName' => 'anotherConnection'],
            [
                'resourceName' => 'brokenResourceName',
                'connectionName' => ResourceConnection::DEFAULT_CONNECTION
            ],
            ['resourceName' => 'extendedResourceName', 'connectionName' => 'validConnectionName'],
            ['resourceName' => 'validResource', 'connectionName' => 'validConnectionName']
        ];
    }
}
