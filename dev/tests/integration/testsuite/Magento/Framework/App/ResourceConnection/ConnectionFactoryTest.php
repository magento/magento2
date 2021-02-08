<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
        $this->assertClassHasAttribute('logger', get_class($connection));
        $object = new ReflectionClass(get_class($connection));
        $attribute = $object->getProperty('logger');
        $attribute->setAccessible(true);
        $propertyObject = $attribute->getValue($connection);
        $attribute->setAccessible(false);
        $this->assertInstanceOf(\Magento\Framework\DB\LoggerInterface::class, $propertyObject);    }
}
