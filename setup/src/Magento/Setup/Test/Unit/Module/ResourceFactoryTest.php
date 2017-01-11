<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Module;

use \Magento\Setup\Module\ResourceFactory;
use \Magento\Setup\Module\ConnectionFactory;

class ResourceFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ResourceFactory
     */
    private $resourceFactory;

    protected function setUp()
    {
        $serviceLocatorMock = $this->getMockForAbstractClass(
            \Zend\ServiceManager\ServiceLocatorInterface::class,
            ['get']
        );
        $connectionFactory = new ConnectionFactory($serviceLocatorMock);
        $serviceLocatorMock
            ->expects($this->once())
            ->method('get')
            ->with(\Magento\Setup\Module\ConnectionFactory::class)
            ->will($this->returnValue($connectionFactory));
        $this->resourceFactory = new ResourceFactory($serviceLocatorMock);
    }

    public function testCreate()
    {
        $resource = $this->resourceFactory->create(
            $this->getMock(\Magento\Framework\App\DeploymentConfig::class, [], [], '', false)
        );
        $this->assertInstanceOf(\Magento\Framework\App\ResourceConnection::class, $resource);
    }
}
