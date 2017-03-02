<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Module;

use \Magento\Setup\Module\ConnectionFactory;

class ConnectionFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConnectionFactory
     */
    private $connectionFactory;

    protected function setUp()
    {
        $serviceLocatorMock = $this->getMockForAbstractClass(
            \Zend\ServiceManager\ServiceLocatorInterface::class,
            ['get']
        );
        $this->connectionFactory = new ConnectionFactory($serviceLocatorMock);
    }

    /**
     * @param array $config
     * @dataProvider dataProviderCreateNoActiveConfig
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage MySQL adapter: Missing required configuration option 'host'
     */
    public function testCreateNoActiveConfig($config)
    {
        $this->connectionFactory->create($config);
    }

    /**
     * @return array
     */
    public function dataProviderCreateNoActiveConfig()
    {
        return [
            [[]],
            [['value']],
            [['active' => 0]],
        ];
    }
}
