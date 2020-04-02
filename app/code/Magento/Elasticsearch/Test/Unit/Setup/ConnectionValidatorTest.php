<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\Setup;

use Magento\AdvancedSearch\Model\Client\ClientResolver;
use Magento\Elasticsearch\Setup\ConnectionValidator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Elasticsearch\Elasticsearch5\Model\Client\Elasticsearch;
use PHPUnit\Framework\TestCase;

class ConnectionValidatorTest extends TestCase
{
    private $connectionValidator;

    private $clientResolverMock;

    private $elasticsearchClientMock;

    protected function setUp()
    {
        $this->clientResolverMock = $this->getMockBuilder(ClientResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->elasticsearchClientMock = $this->getMockBuilder(Elasticsearch::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->connectionValidator = $objectManager->getObject(
            ConnectionValidator::class,
            [
                'clientResolver' => $this->clientResolverMock
            ]
        );
    }

    public function testValidate()
    {
        $configuration = [
            'search-engine' => 'elasticsearch5',
            'elasticsearch-host' => 'localhost',
            'elasticsearch-port' => '9200',
            'elasticsearch-index-prefix' => 'm2',
            'elasticsearch-enable-auth' => false,
            'elasticsearch-timeout' => 20
        ];
        $mappedConfig = [
            'hostname' => 'localhost',
            'port' => '9200',
            'index' => 'm2',
            'enableAuth' => false,
            'username' => null,
            'password' => null,
            'timeout' => 20
        ];

        $this->clientResolverMock
            ->expects($this->once())
            ->method('create')
            ->with($configuration['search-engine'], $mappedConfig)
            ->willReturn($this->elasticsearchClientMock);
        $this->elasticsearchClientMock->expects($this->once())->method('testConnection')->willReturn(true);

        $this->assertTrue($this->connectionValidator->validate($configuration));
    }

    public function testValidateFail()
    {
        $configuration = [
            'search-engine' => 'elasticsearch5',
            'elasticsearch-host' => 'localhost',
            'elasticsearch-port' => '9200',
            'elasticsearch-index-prefix' => 'm2',
            'elasticsearch-enable-auth' => true,
            'elasticsearch-timeout' => 20
        ];
        $mappedConfig = [
            'hostname' => 'localhost',
            'port' => '9200',
            'index' => 'm2',
            'enableAuth' => true,
            'username' => null,
            'password' => null,
            'timeout' => 20
        ];

        $this->clientResolverMock
            ->expects($this->once())
            ->method('create')
            ->with($configuration['search-engine'], $mappedConfig)
            ->willReturn($this->elasticsearchClientMock);
        $this->elasticsearchClientMock->expects($this->once())->method('testConnection')->willReturn(false);

        $this->assertFalse($this->connectionValidator->validate($configuration));
    }
}
