<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Module;

use Laminas\ServiceManager\ServiceLocatorInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Module\ConnectionFactory;
use Magento\Setup\Module\ResourceFactory;
use PHPUnit\Framework\TestCase;

class ResourceFactoryTest extends TestCase
{
    /**
     * @var ResourceFactory
     */
    private $resourceFactory;

    protected function setUp(): void
    {
        $serviceLocatorMock = $this->getMockForAbstractClass(
            ServiceLocatorInterface::class,
            ['get']
        );
        $connectionFactory = new ConnectionFactory($serviceLocatorMock);
        $serviceLocatorMock
            ->expects($this->once())
            ->method('get')
            ->with(ConnectionFactory::class)
            ->will($this->returnValue($connectionFactory));
        $this->resourceFactory = new ResourceFactory($serviceLocatorMock);
    }

    public function testCreate()
    {
        $resource = $this->resourceFactory->create(
            $this->createMock(DeploymentConfig::class)
        );
        $this->assertInstanceOf(ResourceConnection::class, $resource);
    }
}
