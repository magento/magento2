<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
        $serviceLocatorMock = $this->getMockForAbstractClass('Zend\ServiceManager\ServiceLocatorInterface', ['get']);
        $this->connectionFactory = new ConnectionFactory($serviceLocatorMock);
    }

    /**
     * @param array $config
     * @dataProvider dataProviderCreateNoActiveConfig
     */
    public function testCreateNoActiveConfig($config)
    {
        $this->assertNull($this->connectionFactory->create($config));
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
