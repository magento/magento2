<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\ResourceConnection;

use ReflectionClass;

class ConnectionFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\ResourceConnection\ConnectionFactory
     */
    private $model;

    protected function setUp(): void
    {
        $this->model = new \Magento\Framework\App\ResourceConnection\ConnectionFactory(
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
        $this->assertInstanceOf(\Magento\Framework\DB\Adapter\AdapterInterface::class, $connection);
        $this->assertIsObject($connection);
        $this->assertTrue(property_exists($connection, 'logger'));
        $object = new ReflectionClass(get_class($connection));
        $attribute = $object->getProperty('logger');
        $propertyObject = $attribute->getValue($connection);
        $this->assertInstanceOf(\Magento\Framework\DB\LoggerInterface::class, $propertyObject);
    }
}
