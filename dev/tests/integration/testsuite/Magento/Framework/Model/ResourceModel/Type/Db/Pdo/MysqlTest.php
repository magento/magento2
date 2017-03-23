<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\ResourceModel\Type\Db\Pdo;

class MysqlTest extends \PHPUnit_Framework_TestCase
{
    public function testGetConnection()
    {
        $db = \Magento\TestFramework\Helper\Bootstrap::getInstance()->getBootstrap()->getApplication()->getDbInstance();
        $config = [
            'profiler' => [
                'class' => \Magento\Framework\DB\Profiler::class,
                'enabled' => true,
            ],
            'type' => 'pdo_mysql',
            'host' => $db->getHost(),
            'username' => $db->getUser(),
            'password' => $db->getPassword(),
            'dbname' => $db->getSchema(),
            'active' => true,
        ];
        /** @var \Magento\Framework\Model\ResourceModel\Type\Db\Pdo\Mysql $object */
        $object = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\Model\ResourceModel\Type\Db\Pdo\Mysql::class,
            ['config' => $config]
        );

        $connection = $object->getConnection(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                \Magento\Framework\DB\LoggerInterface::class
            )
        );
        $this->assertInstanceOf(\Magento\Framework\DB\Adapter\Pdo\Mysql::class, $connection);
        $profiler = $connection->getProfiler();
        $this->assertInstanceOf(\Magento\Framework\DB\Profiler::class, $profiler);
    }
}
