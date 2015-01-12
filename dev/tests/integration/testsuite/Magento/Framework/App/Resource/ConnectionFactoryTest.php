<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Resource;

class ConnectionFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Resource\ConnectionFactory
     */
    private $model;

    protected function setUp()
    {
        $this->model = new \Magento\Framework\App\Resource\ConnectionFactory(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
        );
    }

    public function testCreate()
    {
        $dbInstance = \Magento\TestFramework\Helper\Bootstrap::getInstance()
            ->getBootstrap()
            ->getApplication()
            ->getDbInstance();
        $dbConfig = [
            'host' => $dbInstance->getHost(),
            'username' => $dbInstance->getUser(),
            'password' => $dbInstance->getPassword(),
            'dbname' => $dbInstance->getSchema(),
            'active' => true,
        ];
        $connection = $this->model->create($dbConfig);
        $this->assertInstanceOf('\Magento\Framework\DB\Adapter\AdapterInterface', $connection);
        $this->assertAttributeInstanceOf('\Magento\Framework\Cache\FrontendInterface', '_cacheAdapter', $connection);
        $this->assertAttributeInstanceOf('\Magento\Framework\Db\LoggerInterface', 'logger', $connection);
    }
}
